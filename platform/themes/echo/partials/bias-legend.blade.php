@php
    /**
     * Bias Legend — explains L/C/R badges + links to comparatif/angles-morts.
     */
@endphp

<aside class="bias-legend glass-panel p-3 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <strong class="text-uppercase small d-inline-flex align-items-center gap-1">
            {{ __('Décoder les biais') }}
            @include(Theme::getThemeNamespace('partials.info-pill'), [
                'size' => 'sm',
                'tone' => 'soft',
                'body' => __("Le biais éditorial classe une source comme Gauche, Centre ou Droite. Basé sur les positions historiques des éditoriaux de la source — pas sur l'article individuel. Un Angle mort signale un dossier qu'un seul camp couvre."),
            ])
        </strong>
        {{-- Wave DDDDDDDDDDD (Vader 2026-05-23) — Middle Ground / Juste
            milieu added as 5th chip. Distinct from Blindspot: Middle
            Ground = covered equally from left AND right (a different
            editorial signal — both extremes converge). Blindspot =
            covered exclusively from ONE side. Two separate purples
            so readers can tell them apart at a glance:
              Middle Ground = #a855f7 (lighter purple, matches
                GrimbaClusterBias::resolve helper)
              Blindspot     = #8a2be2 (deeper blueviolet, pre-existing) --}}
        {{-- Wave YYY (Vader 2026-05-26) — Juste milieu + Angle mort
             chips now clickable deep-links into their dedicated feed
             pages. Partisan chips stay as spans — there's no
             "filter dossiers by left-only" surface today, so making
             them clickable would lead nowhere. --}}
        <div class="d-flex gap-3 small flex-wrap">
            <span style="color:#3b82f6;font-weight:600;">● {{ __('Gauche') }}</span>
            <span style="color:#22c55e;font-weight:600;">● {{ __('Centre') }}</span>
            <span style="color:#ef4444;font-weight:600;">● {{ __('Droite') }}</span>
            <a href="{{ url('/juste-milieu') }}" style="color:#a855f7;font-weight:600;text-decoration:none;" aria-label="{{ __('Voir le flux Juste milieu') }}">● {{ __('Juste milieu') }} →</a>
            <a href="{{ url('/angles-morts') }}" style="color:#8a2be2;font-weight:600;text-decoration:none;" aria-label="{{ __('Voir le flux Angles morts') }}">● {{ __('Angle mort') }} →</a>
        </div>
    </div>
    <p class="small opacity-85 mb-2">
        {{ __("Chaque article est classé selon l'orientation éditoriale de sa source.") }}
        {{ __('Voir la') }}
        {{-- Wave YYY (Vader 2026-05-26) — pointed at /methodology (404)
             instead of the live FR route /methodologie. Every reader
             who tapped this link from a category/feed page hit a
             404. Real live bug — bias-legend ships site-wide. --}}
        <a href="{{ url('/methodologie') }}" class="text-decoration-underline">{{ __('méthodologie') }}</a>,
        {{ __('la') }}
        <a href="{{ url('/dossiers') }}" class="text-decoration-underline">{{ __('comparaison des sources') }}</a>,
        {{ __('le flux des') }}
        <a href="{{ url('/angles-morts') }}" class="text-decoration-underline">{{ __('angles morts') }}</a>
        {{ __('ou le') }}
        <a href="{{ url('/juste-milieu') }}" class="text-decoration-underline">{{ __('juste milieu') }}</a>.
    </p>
</aside>
