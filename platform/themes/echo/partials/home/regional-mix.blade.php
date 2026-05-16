@php
    /**
     * International Edition — Regional Mix.
     *
     * Vader 2026-05-16: "We can further break down the international
     * category (default when accessing the site) to include specific
     * regions top news stories."
     *
     * Three mini-rails — Afrique / Europe / Amériques — each carrying
     * three top stories from that region. Only renders when the active
     * edition is International; named editions hide it (their entire
     * page is already region-focused).
     */

    use App\Support\GrimbaHomeFeed;
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post as BlogPost;

    if (GrimbaHomeFeed::activeRegion() !== 'international') {
        return;
    }

    $mix = GrimbaHomeFeed::regionalMix();
    if (empty($mix)) {
        return;
    }

    $totalPicked = collect($mix)->sum(fn ($c) => $c->count());
    if ($totalPicked === 0) {
        return;
    }

    $regionMeta = [
        'africa'   => ['label' => __('Afrique'),   'tint' => '#c0392b', 'flag' => '◆', 'href' => '/?region=africa'],
        'europe'   => ['label' => __('Europe'),    'tint' => '#3b82f6', 'flag' => '◉', 'href' => '/?region=europe'],
        'americas' => ['label' => __('Amériques'), 'tint' => '#16a34a', 'flag' => '◈', 'href' => '/?region=americas'],
    ];

    $__mixPostIds = collect($mix)->flatten(1)->pluck('id')->map(fn ($id) => (int) $id)->unique()->values();
    $__mixUrls = $__mixPostIds->isEmpty()
        ? collect()
        : \Illuminate\Support\Facades\DB::table('slugs')
            ->whereIn('reference_id', $__mixPostIds->all())
            ->where('reference_type', BlogPost::class)
            ->whereIn('prefix', ['article', 'blog'])
            ->orderByRaw("CASE prefix WHEN 'article' THEN 0 ELSE 1 END")
            ->get(['reference_id', 'key'])
            ->unique('reference_id')
            ->mapWithKeys(fn ($s) => [(int) $s->reference_id => url('/article/' . $s->key)]);
@endphp

<section class="grimba-regional-mix mb-4" aria-labelledby="grimba-regional-mix-title">
    <header class="grimba-regional-mix__head">
        <span class="grimba-regional-mix__kicker">{{ __('Édition internationale') }}</span>
        <h2 id="grimba-regional-mix-title" class="grimba-regional-mix__title">
            {{ __('Les régions, en bref') }}
        </h2>
        <p class="grimba-regional-mix__lede">
            {{ __('Trois histoires phares par région. Choisissez une édition pour zoomer.') }}
        </p>
    </header>

    <div class="grimba-regional-mix__grid">
        @foreach($regionMeta as $key => $meta)
            @php
                $posts = $mix[$key] ?? collect();
                if ($posts->isEmpty()) continue;
            @endphp
            <article class="grimba-regional-mix__column" style="--mix-tint: {{ $meta['tint'] }};">
                <header class="grimba-regional-mix__column-head">
                    <span class="grimba-regional-mix__flag" aria-hidden="true">{{ $meta['flag'] }}</span>
                    <strong class="grimba-regional-mix__label">{{ $meta['label'] }}</strong>
                    <a href="{{ $meta['href'] }}"
                       class="grimba-regional-mix__more"
                       title="{{ __('Voir l\'édition :region', ['region' => $meta['label']]) }}">
                        {{ __('Voir l\'édition') }} →
                    </a>
                </header>

                <ol class="grimba-regional-mix__list">
                    @foreach($posts as $idx => $p)
                        @php
                            $title = GnTr::title($p);
                            $publishedAt = GnTr::publishedAt($p);
                            $url = $__mixUrls->get((int) $p->id) ?: ($p->url ?: '#');
                        @endphp
                        <li class="grimba-regional-mix__item">
                            <span class="grimba-regional-mix__rank" aria-hidden="true">{{ $idx + 1 }}</span>
                            <div class="grimba-regional-mix__body">
                                <a href="{{ $url }}" class="grimba-regional-mix__headline">{{ $title }}</a>
                                <div class="grimba-regional-mix__meta">
                                    @if($p->source_name)
                                        <span>{{ $p->source_name }}</span>
                                    @endif
                                    @if($publishedAt)
                                        <span class="opacity-50">·</span>
                                        <span>{{ $publishedAt->locale(app()->getLocale())->diffForHumans(['short' => true]) }}</span>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </article>
        @endforeach
    </div>
</section>

<style>
    .grimba-regional-mix__head {
        margin-bottom: 14px;
    }

    .grimba-regional-mix__kicker {
        display: inline-block;
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: var(--gn-ink-muted, rgba(26, 23, 19, .58));
        margin-bottom: 4px;
    }

    .grimba-regional-mix__title {
        margin: 0;
        font-family: 'Fraunces', 'Playfair Display', Georgia, serif;
        font-weight: 800;
        font-size: clamp(20px, 2.2vw, 28px);
        line-height: 1.1;
        letter-spacing: -0.02em;
        color: var(--gn-ink, #1a1713);
    }

    .grimba-regional-mix__lede {
        margin: 6px 0 0;
        color: var(--gn-ink-muted, rgba(26, 23, 19, .62));
        font-size: 13.5px;
        line-height: 1.4;
    }

    .grimba-regional-mix__grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .grimba-regional-mix__column {
        position: relative;
        padding: 16px 16px 14px;
        border-radius: 18px;
        background:
            radial-gradient(70% 50% at 0% 0%, color-mix(in srgb, var(--mix-tint, #a8a8a8) 12%, transparent), transparent 70%),
            rgba(255, 255, 255, .62);
        border: 1px solid color-mix(in srgb, var(--mix-tint, #a8a8a8) 22%, rgba(26, 23, 19, .08));
        overflow: hidden;
    }

    .grimba-regional-mix__column::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(
            90deg,
            transparent 0%,
            var(--mix-tint, #a8a8a8) 20%,
            color-mix(in srgb, var(--mix-tint, #a8a8a8) 60%, #fff) 50%,
            var(--mix-tint, #a8a8a8) 80%,
            transparent 100%
        );
    }

    .grimba-regional-mix__column-head {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }

    .grimba-regional-mix__flag {
        color: var(--mix-tint, #a8a8a8);
        font-size: 14px;
        font-weight: 800;
    }

    .grimba-regional-mix__label {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 800;
        font-size: 17px;
        letter-spacing: -0.01em;
        color: var(--gn-ink, #1a1713);
    }

    .grimba-regional-mix__more {
        margin-left: auto;
        font-family: 'Public Sans', system-ui, sans-serif;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        color: var(--mix-tint, #1a1713);
        text-decoration: none;
        white-space: nowrap;
        transition: transform .18s ease;
    }

    .grimba-regional-mix__more:hover,
    .grimba-regional-mix__more:focus-visible {
        color: var(--mix-tint, #1a1713);
        transform: translateX(2px);
    }

    .grimba-regional-mix__list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .grimba-regional-mix__item {
        display: grid;
        grid-template-columns: 22px minmax(0, 1fr);
        gap: 10px;
        align-items: start;
    }

    .grimba-regional-mix__rank {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 800;
        font-size: 18px;
        line-height: 1;
        color: var(--mix-tint, #a8a8a8);
        opacity: .82;
    }

    .grimba-regional-mix__body {
        min-width: 0;
    }

    .grimba-regional-mix__headline {
        display: block;
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 600;
        font-size: 14.5px;
        line-height: 1.32;
        letter-spacing: -0.005em;
        color: var(--gn-ink, #1a1713);
        text-decoration: none;
        margin-bottom: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }

    .grimba-regional-mix__headline:hover,
    .grimba-regional-mix__headline:focus-visible {
        color: var(--mix-tint, var(--gn-ink, #1a1713));
        text-decoration: underline;
        text-decoration-thickness: 1px;
        text-underline-offset: 2px;
    }

    .grimba-regional-mix__meta {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-wrap: wrap;
        font-family: 'Public Sans', system-ui, sans-serif;
        font-size: 11.5px;
        font-weight: 600;
        color: var(--gn-ink-muted, rgba(26, 23, 19, .58));
    }

    /* Dark-mode parity */
    [data-bs-theme="dark"] .grimba-regional-mix__title,
    body[data-theme="dark"] .grimba-regional-mix__title,
    [data-bs-theme="dark"] .grimba-regional-mix__label,
    body[data-theme="dark"] .grimba-regional-mix__label,
    [data-bs-theme="dark"] .grimba-regional-mix__headline,
    body[data-theme="dark"] .grimba-regional-mix__headline {
        color: #fffaf0;
    }

    [data-bs-theme="dark"] .grimba-regional-mix__column,
    body[data-theme="dark"] .grimba-regional-mix__column {
        background:
            radial-gradient(70% 50% at 0% 0%, color-mix(in srgb, var(--mix-tint, #a8a8a8) 22%, transparent), transparent 70%),
            rgba(28, 24, 17, .68);
        border-color: color-mix(in srgb, var(--mix-tint, #a8a8a8) 38%, rgba(255, 250, 240, .12));
    }

    @media (max-width: 991.98px) {
        .grimba-regional-mix__grid {
            grid-template-columns: 1fr;
        }
    }
</style>
