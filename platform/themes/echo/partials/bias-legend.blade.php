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
        <div class="d-flex gap-3 small">
            <span style="color:#3b82f6;font-weight:600;">● {{ __('Gauche') }}</span>
            <span style="color:#22c55e;font-weight:600;">● {{ __('Centre') }}</span>
            <span style="color:#ef4444;font-weight:600;">● {{ __('Droite') }}</span>
            <span style="color:#a855f7;font-weight:600;">● {{ __('Juste milieu') }}</span>
            <span style="color:#8a2be2;font-weight:600;">● {{ __('Angle mort') }}</span>
        </div>
    </div>
    <p class="small opacity-85 mb-2">
        {{ __("Chaque article est classé selon l'orientation éditoriale de sa source.") }}
        {{ __('Voir la') }}
        <a href="{{ url('/methodology') }}" class="text-decoration-underline">{{ __('méthodologie') }}</a>,
        {{ __('la') }}
        <a href="{{ url('/dossiers') }}" class="text-decoration-underline">{{ __('comparaison des sources') }}</a>
        {{ __('ou le flux des') }}
        <a href="{{ url('/angles-morts') }}" class="text-decoration-underline">{{ __('angles morts') }}</a>.
    </p>
</aside>
