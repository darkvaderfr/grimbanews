@php
    /**
     * Country pill — small ISO-2 code badge for any article card.
     *
     * Vader 2026-05-16: "let's add a country pill tag on articles
     * (for each and all current and future) articles especially on
     * the homepage cards, category pages etc. on every article. This
     * will help with the bleed over of articles across regions and
     * narrows the users search for interest."
     *
     * Accepts either a $post (resolves source country automatically)
     * or an explicit $country (ISO-2 string or country label).
     *
     * @var \Botble\Blog\Models\Post|null $post
     * @var string|null $country
     * @var string|null $size  'sm' (default) | 'md'
     */

    $size = $size ?? 'sm';

    $__resolveCountry = null;
    if (isset($country) && $country !== null && $country !== '') {
        $__resolveCountry = trim((string) $country);
    } elseif (isset($post) && $post) {
        if (! empty($post->source_id)) {
            $__resolveCountry = \Illuminate\Support\Facades\Cache::remember(
                'grimba_country_for_source_v1:' . (int) $post->source_id,
                3600,
                fn () => \Illuminate\Support\Facades\DB::table('news_sources')
                    ->where('id', $post->source_id)
                    ->value('country')
            );
        }
        if (! $__resolveCountry && ! empty($post->country)) {
            $__resolveCountry = $post->country;
        }
    }

    if (! $__resolveCountry) return;

    $__code = strtoupper(trim((string) $__resolveCountry));
    if ($__code === '') return;

    // Optional friendly label (the GrimbaSourceBreakdown helper has a
    // country-name lookup the dossier already uses).
    $__label = $__code;
    try {
        $__friendly = \App\Support\GrimbaSourceBreakdown::countryLabel($__code);
        if ($__friendly && $__friendly !== '—') {
            $__label = $__friendly;
        }
    } catch (\Throwable) {
        // Fall back to the raw ISO-2.
    }
@endphp

<span class="grimba-country-pill grimba-country-pill--{{ $size }}"
      title="{{ $__label }}"
      aria-label="{{ __('Pays source : :country', ['country' => $__label]) }}">
    {{ $__code }}
</span>

@once
    <style>
        .grimba-country-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2ch;
            padding: 1px 5px;
            border-radius: 4px;
            background: rgba(26, 23, 19, .06);
            border: 1px solid rgba(26, 23, 19, .10);
            color: var(--gn-ink-muted, rgba(26, 23, 19, .65));
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            line-height: 1.4;
            vertical-align: 1px;
        }

        .grimba-country-pill--md {
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 5px;
        }

        [data-bs-theme="dark"] .grimba-country-pill,
        body[data-theme="dark"] .grimba-country-pill {
            background: rgba(255, 250, 240, .10);
            border-color: rgba(255, 250, 240, .14);
            color: rgba(255, 250, 240, .68);
        }
    </style>
@endonce
