@php
    use App\Support\GrimbaPostRecency;
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post;
    use Illuminate\Support\Str;

    $breakingWindowHours = max(1, (int) setting('grimba_breaking_window_hours', 6));

    // Region-scoped per Vader 2026-05-16: editorial surfaces for an
    // edition show only that edition's content. Breaking ticker is
    // editorial. International short-circuits the scope to "no filter".
    $__breakingRegion = \App\Ground\Regions::migrate(
        (string) request()->cookie(\App\Scopes\GrimbaRegionScope::COOKIE_NAME, 'international')
    );

    $breakingPosts = \Illuminate\Support\Facades\Cache::remember(
        'grimba_breaking_ticker_v4:' . GnTr::targetLocale() . ':' . $__breakingRegion . ':' . $breakingWindowHours,
        45,
        function () use ($breakingWindowHours) {
            $cols = ['id','name','translated_name','translated_description','translated_to','original_language','description','content','summary_nobuai','source_name','source_id','bias_rating','published_at','created_at','image'];

            $recentQuery = Post::query()
                ->where('status', 'published')
                ->whereNotNull('source_name')
                ->with('slugable');
            GrimbaPostRecency::wherePublishedSince($recentQuery, now()->subHours($breakingWindowHours));
            GrimbaPostRecency::orderByPublished($recentQuery);

            $recent = $recentQuery->limit(14)->get($cols);
            if ($recent->isNotEmpty()) {
                return $recent;
            }

            $fallbackQuery = Post::query()
                ->where('status', 'published')
                ->whereNotNull('source_name')
                ->with('slugable');
            GrimbaPostRecency::wherePublishedSince($fallbackQuery, now()->subDay());
            GrimbaPostRecency::orderByPublished($fallbackQuery);

            $fallback = $fallbackQuery->limit(14)->get($cols);
            if ($fallback->isNotEmpty()) {
                return $fallback;
            }

            $latestQuery = Post::query()
                ->where('status', 'published')
                ->with('slugable');
            GrimbaPostRecency::orderByPublished($latestQuery);

            return $latestQuery->limit(10)->get($cols);
        }
    );

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

    // Source rendered as a short uppercased monogram (1-2 words max) per
    // Vader 2026-05-16: ticker should read like a CNN crawl, not a
    // truncated headline. "Le Monde" → "LE MONDE", "Yahoo Sports" → "YAHOO".
    $__shortSource = static function ($post) use ($__sourceNames): string {
        // Prefer the canonical publisher name from news_sources over
        // posts.source_name (which is sometimes the upstream domain).
        $name = '';
        if (! empty($post->source_id) && isset($__sourceNames[$post->source_id])) {
            $name = trim((string) $__sourceNames[$post->source_id]);
        }
        if ($name === '') {
            $name = trim((string) ($post->source_name ?? ''));
        }
        if ($name === '') return 'GRIMBANEWS';

        // If it still looks like a domain (contains a dot or TLD),
        // collapse to the registrable label only.
        if (preg_match('/^[a-z0-9-]+(\\.[a-z]{2,})+$/i', $name)) {
            $parts = explode('.', $name);
            $name = $parts[count($parts) - 2] ?? $name;
        }

        // Drop common publisher noise prefixes.
        $name = preg_replace('/^(the|le|la|les|l\')\\s+/iu', '', $name) ?? $name;

        $words = preg_split('/\\s+/u', $name) ?: [$name];
        $words = array_slice($words, 0, 2);

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

    $__summaryFor = static function ($post) use ($__firstSentence, $__looksLikeJunk): string {
        // 1. NobuAI synthesis — already condensed by the agent. First
        //    pick when present.
        $nobu = trim((string) ($post->summary_nobuai ?? ''));
        if ($nobu !== '') {
            $sentence = $__firstSentence($nobu);
            if ($sentence !== '' && ! $__looksLikeJunk($sentence)) {
                return $sentence;
            }
        }

        // 2. Title — guaranteed editorial sentence in the active locale.
        //    Cleaner than raw scraped descriptions for the launch while
        //    NobuAI summaries fill in.
        $title = trim((string) GnTr::title($post));

        // 3. Use translated_description / description ONLY when they
        //    don't look like player chrome / junk. Otherwise fall back
        //    to the title.
        foreach ([
            (string) GnTr::description($post),
            (string) ($post->description ?? ''),
        ] as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '') continue;
            $sentence = $__firstSentence($candidate);
            if ($sentence === '' || $__looksLikeJunk($sentence)) continue;
            // Prefer the description only when it's meaningfully longer
            // than the title (i.e., adds context, doesn't duplicate).
            if (mb_strlen($sentence) > mb_strlen($title) + 24) {
                return $sentence;
            }
        }

        return Str::limit($title, 140);
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

<div class="grimba-breaking grimba-urgency" role="region" aria-label="{{ __('Dernières nouvelles') }}" data-grimba-breaking>
    <div class="container-xxl grimba-breaking__inner">
        <div class="grimba-breaking__lede">
            <span class="grimba-breaking__eyebrow">{{ __('En direct') }}</span>
            <span class="grimba-breaking__headline" data-grimba-breaking-headline>{{ $breakingItems->first()['summary'] }}</span>
        </div>

        <div class="grimba-breaking__viewport" aria-hidden="true">
            <div class="grimba-breaking__track">
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
                                @if($item['time'] !== '')
                                    <span class="grimba-breaking__time">{{ $item['time'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>

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
