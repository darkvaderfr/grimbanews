@php
    use App\Support\GrimbaPostRecency;
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post;
    use Illuminate\Support\Str;

    $breakingWindowHours = max(1, (int) setting('grimba_breaking_window_hours', 6));
    $breakingPosts = \Illuminate\Support\Facades\Cache::remember(
        'grimba_breaking_ticker_v3:' . GnTr::targetLocale() . ':' . $breakingWindowHours,
        45,
        function () use ($breakingWindowHours) {
            $recentQuery = Post::withoutGlobalScope('grimba_region')
                ->where('status', 'published')
                ->whereNotNull('source_name')
                ->with('slugable');
            GrimbaPostRecency::wherePublishedSince($recentQuery, now()->subHours($breakingWindowHours));
            GrimbaPostRecency::orderByPublished($recentQuery);

            $recent = $recentQuery->limit(14)->get();
            if ($recent->isNotEmpty()) {
                return $recent;
            }

            $fallbackQuery = Post::withoutGlobalScope('grimba_region')
                ->where('status', 'published')
                ->whereNotNull('source_name')
                ->with('slugable');
            GrimbaPostRecency::wherePublishedSince($fallbackQuery, now()->subDay());
            GrimbaPostRecency::orderByPublished($fallbackQuery);

            $fallback = $fallbackQuery->limit(14)->get();
            if ($fallback->isNotEmpty()) {
                return $fallback;
            }

            $latestQuery = Post::withoutGlobalScope('grimba_region')
                ->where('status', 'published')
                ->with('slugable');
            GrimbaPostRecency::orderByPublished($latestQuery);

            return $latestQuery->limit(10)->get();
        }
    );

    GnTr::warm($breakingPosts);

    $biasDot = [
        'left' => '#3b82f6',
        'center' => '#a8a8a8',
        'right' => '#e84c3d',
    ];

    $breakingTotal = max(1, $breakingPosts->count());
    $breakingItems = $breakingPosts
        ->values()
        ->map(function ($post, int $index) use ($breakingTotal, $biasDot): array {
            $title = trim((string) GnTr::title($post));
            $publishedAt = GnTr::publishedAt($post);
            $bias = $post->bias_rating ?? 'unknown';
            // Brighter for fresher items: 1.0 at the head, fading to 0.55
            // at the tail. Keeps the rail readable without making old
            // items disappear.
            $alpha = round(1.0 - ($index / max(1, $breakingTotal - 1)) * 0.45, 3);

            return [
                'title' => $title !== '' ? $title : (string) __('Nouvelle histoire'),
                'url' => $post->url,
                'source' => trim((string) ($post->source_name ?? '')) ?: 'GrimbaNews',
                'time' => $publishedAt ? $publishedAt->locale(app()->getLocale())->diffForHumans() : '',
                'bias' => isset($biasDot[$bias]) ? $bias : null,
                'bias_color' => $biasDot[$bias] ?? null,
                'alpha' => $alpha,
            ];
        })
        ->filter(fn (array $item): bool => trim($item['title']) !== '')
        ->values();

    if ($breakingItems->isEmpty()) {
        $breakingItems = collect([[
            'title' => __('Voyez chaque angle de chaque histoire.'),
            'url' => url('/search'),
            'source' => 'GrimbaNews',
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
            <span class="grimba-breaking__headline" data-grimba-breaking-headline>{{ $breakingItems->first()['title'] }}</span>
        </div>

        <div class="grimba-breaking__viewport" aria-hidden="true">
            <div class="grimba-breaking__track">
                @for($i = 0; $i < 2; $i++)
                    <div class="grimba-breaking__group">
                        @foreach($breakingItems as $item)
                            <a href="{{ $item['url'] }}"
                               class="grimba-breaking__item"
                               data-breaking-item-title="{{ $item['title'] }}"
                               style="--gn-break-alpha: {{ $item['alpha'] }};{{ $item['bias_color'] ? ' --gn-break-bias: ' . $item['bias_color'] . ';' : '' }}">
                                @if(! empty($item['bias_color']))
                                    <span class="grimba-breaking__dot" aria-hidden="true"></span>
                                @endif
                                <span class="grimba-breaking__source">{{ $item['source'] }}</span>
                                <span class="grimba-breaking__title">{{ Str::limit($item['title'], 96) }}</span>
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
    .grimba-breaking__item {
        opacity: var(--gn-break-alpha, 1);
        transition: opacity .2s ease, transform .2s ease;
    }

    .grimba-breaking__item:hover,
    .grimba-breaking__item:focus-visible {
        opacity: 1 !important;
        transform: translateY(-1px);
    }

    .grimba-breaking__dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        margin-right: 6px;
        border-radius: 50%;
        background: var(--gn-break-bias, #a8a8a8);
        box-shadow: 0 0 0 2px rgba(255, 255, 255, .42), 0 0 10px color-mix(in srgb, var(--gn-break-bias, #a8a8a8) 60%, transparent);
        flex-shrink: 0;
        vertical-align: middle;
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
