@php
    use App\Support\GrimbaPostRecency;
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post;
    use Illuminate\Support\Str;

    // Hard 18h cap on what's eligible for the breaking rail per Vader
    // 2026-05-16. Older than 18h reads as stale on a "live" surface.
    $breakingWindowHours = min(18, max(1, (int) setting('grimba_breaking_window_hours', 18)));

    // Region-scoped per Vader 2026-05-16: editorial surfaces for an
    // edition show only that edition's content. Breaking ticker is
    // editorial. International short-circuits the scope to "no filter".
    $__breakingRegion = \App\Ground\Regions::migrate(
        (string) request()->cookie(\App\Scopes\GrimbaRegionScope::COOKIE_NAME, 'international')
    );

    // Real-breaking phrase set (FR + EN). Editorially-loaded markers
    // that distinguish "we are interrupting normal coverage" from
    // routine reporting. Single ambiguous words ("breaking", "urgent",
    // "fire") match too liberally — "ground-breaking", "urgent
    // refresh", "fire department announcement" are NOT breaking news.
    // Phrases below only fire on intentional editorial usage.
    $__breakingKeywords = [
        'breaking news', 'breaking:', 'just in:', 'live updates',
        'state of emergency', 'declared dead', 'evacuation order',
        'mass casualty', 'death toll', 'massive explosion',
        'en direct', 'dernière minute', 'flash info', 'alerte info',
        'alerte enlèvement', "état d'urgence", 'urgent :', 'urgent –',
        'plan blanc', 'attentat', 'sous les décombres',
    ];

    // Substring match (case-insensitive). Since the keyword set is
    // multi-word phrases now, naive substring is precise enough — no
    // word-boundary trickery needed, and 'ground-breaking' can't
    // accidentally match 'breaking news'.
    $__breakingRegex = '/(?:' .
        implode('|', array_map(fn ($kw) => preg_quote($kw, '/'), $__breakingKeywords)) .
        ')/iu';

    // Track whether the ticker is in REAL-BREAKING mode vs LATEST-FALLBACK
    // so the eyebrow can be honest. Vader 2026-05-16 + Echo audit:
    // labeling normal recent posts as "Live" is exactly the firehose
    // problem we just fixed.
    $__breakingMode = 'real';
    $__cacheBundle = \Illuminate\Support\Facades\Cache::remember(
        'grimba_breaking_ticker_v8:' . GnTr::targetLocale() . ':' . $__breakingRegion . ':' . $breakingWindowHours,
        45,
        function () use ($breakingWindowHours, $__breakingKeywords, $__breakingRegex) {
            $cols = ['id','name','translated_name','translated_description','translated_to','original_language','description','content','summary_nobuai','source_name','source_id','bias_rating','published_at','created_at','image'];

            // 1) Strict breaking-news pass: SQL pre-filters via LIKE on
            //    title fields only (descriptions contain subscribe-prompt
            //    boilerplate like "Get our breaking news alerts" that
            //    would otherwise pollute the ticker with non-breaking
            //    coverage). PHP regex finalises the match against title
            //    proper. Wider 24h window since breaking stories
            //    legitimately run beyond a 6h cycle.
            $candidatesQuery = Post::query()
                ->where('status', 'published')
                ->whereNotNull('source_name')
                ->where(function ($q) use ($__breakingKeywords): void {
                    foreach ($__breakingKeywords as $kw) {
                        $like = '%' . $kw . '%';
                        $q->orWhere('name', 'like', $like)
                          ->orWhere('translated_name', 'like', $like);
                    }
                })
                ->with('slugable');
            GrimbaPostRecency::wherePublishedSince($candidatesQuery, now()->subHours(24));
            GrimbaPostRecency::orderByPublished($candidatesQuery);

            // Over-fetch since post-filter will drop false positives.
            $candidates = $candidatesQuery->limit(40)->get($cols);

            $breaking = $candidates->filter(function ($post) use ($__breakingRegex): bool {
                $haystack = mb_strtolower(trim((string) ($post->name ?? '') . ' ' . (string) ($post->translated_name ?? '')));
                return (bool) preg_match($__breakingRegex, $haystack);
            })->take(14);

            if ($breaking->isNotEmpty()) {
                return ['mode' => 'real', 'posts' => $breaking->values()];
            }

            // 2) No live breaking — surface the freshest recent posts so
            //    the rail still moves. Mode flips to 'latest' so the
            //    eyebrow can render honestly.
            $recentFallback = Post::query()
                ->where('status', 'published')
                ->whereNotNull('source_name')
                ->with('slugable');
            GrimbaPostRecency::wherePublishedSince($recentFallback, now()->subHours($breakingWindowHours));
            GrimbaPostRecency::orderByPublished($recentFallback);

            $recent = $recentFallback->limit(14)->get($cols);
            if ($recent->isNotEmpty()) {
                return ['mode' => 'latest', 'posts' => $recent];
            }

            $dailyFallback = Post::query()
                ->where('status', 'published')
                ->whereNotNull('source_name')
                ->with('slugable');
            GrimbaPostRecency::wherePublishedSince($dailyFallback, now()->subDay());
            GrimbaPostRecency::orderByPublished($dailyFallback);

            $daily = $dailyFallback->limit(14)->get($cols);
            if ($daily->isNotEmpty()) {
                return ['mode' => 'latest', 'posts' => $daily];
            }

            $latestQuery = Post::query()
                ->where('status', 'published')
                ->with('slugable');
            GrimbaPostRecency::orderByPublished($latestQuery);

            return ['mode' => 'latest', 'posts' => $latestQuery->limit(10)->get($cols)];
        }
    );

    $__breakingMode = $__cacheBundle['mode'] ?? 'latest';
    $breakingPosts = $__cacheBundle['posts'] ?? collect();

    GnTr::warm($breakingPosts);

    // Pre-load clean source names from news_sources for any posts where
    // source_name looks like a domain (uk.news.yahoo.com style scrapes
    // from RSS aliases). The publisher's editorial name lives in
    // news_sources.name keyed by posts.source_id.
    $__sourceIds = $breakingPosts->pluck('source_id')->filter()->unique()->all();
    $__sourceNames = empty($__sourceIds)
        ? collect()
        : \Illuminate\Support\Facades\DB::table('news_sources')
            ->whereIn('id', $__sourceIds)
            ->pluck('name', 'id');

    // Source rendered as a clean uppercased monogram. Per Vader 2026-05-16:
    // "1 or 2 words for the providers/source" — but "Times of Israel"
    // truncated to "TIMES OF" reads broken, so we allow a 3rd word when
    // the second is a short connector ("of", "de", "du", "&") that
    // shouldn't end the monogram. Trailing generic suffixes ("Inc",
    // "Group", "News", "Media", "Online") are dropped because they
    // add no signal.
    $__shortSource = static function ($post) use ($__sourceNames): string {
        $name = '';
        if (! empty($post->source_id) && isset($__sourceNames[$post->source_id])) {
            $name = trim((string) $__sourceNames[$post->source_id]);
        }
        if ($name === '') {
            $name = trim((string) ($post->source_name ?? ''));
        }
        if ($name === '') return 'GRIMBANEWS';

        // If it still looks like a domain, collapse to the registrable label.
        if (preg_match('/^[a-z0-9-]+(\\.[a-z]{2,})+$/i', $name)) {
            $parts = explode('.', $name);
            $name = $parts[count($parts) - 2] ?? $name;
        }

        // Drop leading articles.
        $name = preg_replace('/^(the|le|la|les|l\')\\s+/iu', '', $name) ?? $name;

        $words = preg_split('/\\s+/u', $name) ?: [$name];

        // Drop trailing generic suffixes that carry no editorial signal.
        $suffixDrop = ['inc', 'inc.', 'group', 'corp', 'corp.', 'co.', 'ltd', 'sa', 's.a.', 'media', 'news', 'online', 'magazine', 'press'];
        while (count($words) > 1 && in_array(mb_strtolower(end($words)), $suffixDrop, true)) {
            array_pop($words);
        }

        // Connector words that shouldn't end a monogram — bump to 3
        // words when the 2nd is a connector.
        $connectors = ['of', 'de', 'du', 'des', 'la', 'le', 'les', 'and', 'et', '&', 'für', 'von'];
        $cap = 2;
        if (count($words) >= 3 && in_array(mb_strtolower($words[1] ?? ''), $connectors, true)) {
            $cap = 3;
        }
        $words = array_slice($words, 0, $cap);

        return mb_strtoupper(implode(' ', $words));
    };

    // One-sentence summary lifted from NobuAI synthesis when available,
    // else from translated_description, else description, else title.
    // Limited to 140 chars so the ticker reads.
    $__firstSentence = static function (string $text, int $cap = 140): string {
        $text = trim(preg_replace('/\\s+/u', ' ', strip_tags($text))) ?? '';
        if ($text === '') return '';
        // Split on . ! ? followed by space or EOL; keep the first piece.
        $parts = preg_split('/(?<=[\\.\\!\\?])\\s+/u', $text, 2);
        $lead = trim($parts[0] ?? $text);
        if (mb_strlen($lead) < 30 && mb_strlen($text) > 30) {
            // First sentence too short to be useful — take a longer slice.
            $lead = $text;
        }
        return Str::limit($lead, $cap);
    };

    // Junk patterns that signal a scraped description carrying player
    // chrome ("Now Playing"), legal boilerplate ("Story by …"), or ad
    // breaks ("BREAKING:" stub headers). When any of these dominate the
    // first sentence, skip the description and use the title instead —
    // titles are always editorial sentences.
    $__looksLikeJunk = static function (string $text): bool {
        $patterns = [
            '/^now playing/i',
            '/ad playing/i',
            '/^story by\b/i',
            '/^breaking:\s*$/i',
            '/^paused\b/i',
            '/^[0-9:]+\s+now playing/i',
        ];
        foreach ($patterns as $p) {
            if (preg_match($p, $text)) return true;
        }
        return false;
    };

    // Strip common publisher noise from a headline so the ticker reads
    // as a clean editorial sentence rather than a raw RSS title.
    // Removes: leading "Breaking news:", "Live updates:", "Just in:",
    // trailing publisher dash-suffix " - Yahoo Sports" / "- BBC",
    // trailing ellipses + truncation artifacts, "[Podcasts]"-style
    // section tags, "Replay :" prefixes.
    $__cleanTitle = static function (string $title): string {
        $t = trim($title);

        // [Podcasts]/[Replay]/[Vidéo] section tag prefix.
        $t = preg_replace('/^\\[[^\\]]+\\]\\s*/u', '', $t);

        // Prefix: "Breaking news:" / "Live updates:" / "En direct:" etc.
        $t = preg_replace('/^(breaking news|live updates|just in|en direct|flash info|alerte info|dernière minute|replay)\\s*[:\\-–—]\\s*/iu', '', $t);

        // Mid-string "<context> breaking news: <real story>" → keep <real story>.
        // Publisher SEO often prepends "<topic> breaking news:" / "<topic>
        // live updates:" / "<event> news live:" to a headline; the meat
        // is after the colon. Apply only when the marker is the LAST
        // colon in the prefix (so a real "X: y, z" headline isn't gutted).
        if (preg_match('/^(.*?\\b(?:breaking news|live updates|news live|in pictures)\\s*:\\s*)(.+)$/iu', $t, $m)) {
            $rest = trim($m[2]);
            if (mb_strlen($rest) >= 40) {
                $t = $rest;
            }
        }

        // Suffix: trailing publisher signature ("- Yahoo Sports" etc.).
        $t = preg_replace('/\\s*[\\-–—]\\s*(yahoo sports|bbc news|cnn|reuters|associated press|le monde|le figaro|the guardian|nytimes|the times|times of israel|al jazeera|france 24|france 24 \\(.+?\\))\\s*$/iu', '', $t);

        // Trailing dangling separators + truncation artifacts.
        $t = preg_replace('/\\s*[\\-–—]\\s*$/u', '', $t);
        $t = preg_replace('/\\s*\\.\\.\\.\\s*$/u', '', $t);
        $t = preg_replace('/\\s+here.?s what happened.*$/iu', '', $t);

        return trim($t);
    };

    $__summaryFor = static function ($post) use ($__firstSentence, $__looksLikeJunk, $__cleanTitle): string {
        // Pipeline: pick a candidate, clean it, take its first sentence,
        // verify it's not chrome/junk. Cleaning runs on every candidate
        // because scraped descriptions often start with the same
        // publisher boilerplate the title carries.
        $clean = static function (string $raw) use ($__firstSentence, $__looksLikeJunk, $__cleanTitle): ?string {
            $cleaned = $__cleanTitle($raw);
            if ($cleaned === '') return null;
            $sentence = $__firstSentence($cleaned);
            // Re-clean post-extraction so trailing dashes / ellipses /
            // publisher tails introduced inside the first sentence get
            // stripped too.
            $sentence = $__cleanTitle($sentence);
            if ($sentence === '' || $__looksLikeJunk($sentence)) return null;
            return $sentence;
        };

        // 1. NobuAI synthesis — already condensed by the agent.
        $nobu = trim((string) ($post->summary_nobuai ?? ''));
        if ($nobu !== '') {
            $sentence = $clean($nobu);
            if ($sentence) return $sentence;
        }

        // 2. Cleaned title.
        $titleSentence = $clean((string) GnTr::title($post));

        // 3. Description ONLY when meaningfully longer than the cleaned
        //    title and not junk. Cleaned the same way so leading
        //    "X breaking news:" boilerplate is removed before comparing.
        foreach ([
            (string) GnTr::description($post),
            (string) ($post->description ?? ''),
        ] as $candidate) {
            $sentence = $clean($candidate);
            if (! $sentence) continue;
            if ($titleSentence === null || mb_strlen($sentence) > mb_strlen($titleSentence) + 24) {
                return Str::limit($sentence, 160);
            }
        }

        return Str::limit($titleSentence ?? trim((string) GnTr::title($post)), 160);
    };

    $biasDot = [
        'left' => '#3b82f6',
        'center' => '#a8a8a8',
        'right' => '#e84c3d',
    ];

    $breakingTotal = max(1, $breakingPosts->count());
    $breakingItems = $breakingPosts
        ->values()
        ->map(function ($post, int $index) use ($breakingTotal, $biasDot, $__shortSource, $__summaryFor): array {
            $summary = $__summaryFor($post);
            $publishedAt = GnTr::publishedAt($post);
            $bias = $post->bias_rating ?? 'unknown';
            // Brighter for fresher items: 1.0 at the head, fading to 0.55
            // at the tail. Keeps the rail readable without making old
            // items disappear.
            $alpha = round(1.0 - ($index / max(1, $breakingTotal - 1)) * 0.45, 3);

            return [
                'summary' => $summary !== '' ? $summary : (string) __('Nouvelle histoire'),
                'url' => $post->url,
                'source' => $__shortSource($post),
                'time' => $publishedAt ? $publishedAt->locale(app()->getLocale())->diffForHumans() : '',
                'bias' => isset($biasDot[$bias]) ? $bias : null,
                'bias_color' => $biasDot[$bias] ?? null,
                'alpha' => $alpha,
            ];
        })
        ->filter(fn (array $item): bool => trim($item['summary']) !== '')
        ->values();

    if ($breakingItems->isEmpty()) {
        $breakingItems = collect([[
            'summary' => __('Voyez chaque angle de chaque histoire.'),
            'url' => url('/search'),
            'source' => 'GRIMBANEWS',
            'time' => '',
            'bias' => null,
            'bias_color' => null,
            'alpha' => 1.0,
        ]]);
    }
@endphp

<div class="grimba-breaking grimba-urgency grimba-breaking--mode-{{ $__breakingMode }}"
     role="region"
     aria-label="{{ __('Dernières nouvelles') }}"
     data-grimba-breaking
     data-grimba-mode="{{ $__breakingMode }}">
    <div class="container-xxl grimba-breaking__inner">
        <div class="grimba-breaking__lede">
            <span class="grimba-breaking__eyebrow">
                @if($__breakingMode === 'real')
                    {{ __('En direct') }}
                @else
                    {{ __('Dernières') }}
                @endif
            </span>
            <span class="grimba-breaking__headline" data-grimba-breaking-headline>{{ $breakingItems->first()['summary'] }}</span>
        </div>

        @if($breakingItems->count() >= 3)
            <div class="grimba-breaking__viewport" aria-hidden="true" data-grimba-marquee>
                <div class="grimba-breaking__track" data-grimba-marquee-track>
                    @for($i = 0; $i < 2; $i++)
                        <div class="grimba-breaking__group">
                            @foreach($breakingItems as $item)
                                <a href="{{ $item['url'] }}"
                                   class="grimba-breaking__item"
                                   data-breaking-item-title="{{ $item['summary'] }}"
                                   style="--gn-break-alpha: {{ $item['alpha'] }};">
                                    <span class="grimba-breaking__source">{{ $item['source'] }}</span>
                                    <span class="grimba-breaking__sep" aria-hidden="true">—</span>
                                    <span class="grimba-breaking__title">{{ $item['summary'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endfor
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    /*
     * Marquee speed normaliser.
     *
     * Vader 2026-05-17: africa edition felt faster because its track
     * was shorter (fewer items × same 34s animation = much higher
     * effective pixel speed). The animation translates by -50% (one
     * group width) over the duration, so we want a constant px/sec
     * rate regardless of edition. Measure the track width on load and
     * resize, then derive `--gn-marquee-duration` from it.
     */
    (function () {
        const PX_PER_SEC = 60; // constant crawl speed
        const tracks = document.querySelectorAll('[data-grimba-marquee-track]');
        if (! tracks.length) return;

        function tune(track) {
            const half = track.scrollWidth / 2;
            if (! Number.isFinite(half) || half <= 0) return;
            const seconds = Math.max(12, Math.min(120, half / PX_PER_SEC));
            track.style.setProperty('--gn-marquee-duration', seconds.toFixed(2) + 's');
        }

        tracks.forEach(tune);

        // Re-tune on viewport resize (the absolute width of each item
        // changes with breakpoints + font scaling).
        let resizeFrame = null;
        window.addEventListener('resize', () => {
            if (resizeFrame) cancelAnimationFrame(resizeFrame);
            resizeFrame = requestAnimationFrame(() => tracks.forEach(tune));
        }, { passive: true });

        // Web fonts can change layout late — re-tune once after load.
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(() => tracks.forEach(tune)).catch(() => {});
        }
    })();
</script>

<style>
    /* CNN-style breaking crawl: short source monogram, em-dash
       separator, then a clear one-sentence summary. Older items in
       the rail fade slightly so the freshest reads brightest. */
    .grimba-breaking__item {
        opacity: var(--gn-break-alpha, 1);
        transition: opacity .2s ease, transform .2s ease;
        display: inline-flex;
        align-items: baseline;
        gap: 0.5em;
        white-space: nowrap;
    }

    .grimba-breaking__item:hover,
    .grimba-breaking__item:focus-visible {
        opacity: 1 !important;
        transform: translateY(-1px);
    }

    .grimba-breaking__source {
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 0.72em;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
        opacity: .9;
        flex-shrink: 0;
    }

    .grimba-breaking__sep {
        opacity: .42;
        font-weight: 400;
    }

    .grimba-breaking__title {
        font-weight: 600;
        max-width: 70ch;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<script>
    (function () {
        const root = document.querySelector('[data-grimba-breaking]');
        const target = root && root.querySelector('[data-grimba-breaking-headline]');
        if (!root || !target) return;

        const headlines = Array.from(root.querySelectorAll('[data-breaking-item-title]'))
            .map(item => (item.getAttribute('data-breaking-item-title') || item.textContent).replace(/\s+/g, ' ').trim())
            .filter(Boolean);

        if (headlines.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        let index = 0;
        window.setInterval(() => {
            index = (index + 1) % headlines.length;
            target.classList.add('is-changing');
            window.setTimeout(() => {
                target.textContent = headlines[index];
                target.classList.remove('is-changing');
            }, 180);
        }, 5200);
    })();
</script>
