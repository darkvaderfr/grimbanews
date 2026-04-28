@php
    Theme::layout('grimba-chrome');
    /**
     * S156 — /proprietaires ownership map.
     * @var \Illuminate\Support\Collection $owners
     * @var int $totalOwners
     * @var int $totalSources
     */

    $biasMeta = [
        'left'    => ['label' => __('Gauche'),     'color' => '#3b82f6'],
        'center'  => ['label' => __('Centre'),     'color' => '#a8a8a8'],
        'right'   => ['label' => __('Droite'),     'color' => '#e84c3d'],
        'unknown' => ['label' => __('Non classé'), 'color' => '#6b6459'],
    ];
@endphp

<section class="grimba-owners py-5">
    <div class="container">

        <header class="glass-panel p-4 p-md-5 mb-4">
            <span class="grimba-methodology__kicker">{{ __('Propriété des médias') }}</span>
            <h1 class="grimba-methodology__title mt-2 mb-3">
                {{ __('Qui possède quoi') }}
            </h1>
            <p class="mb-2 opacity-85" style="font-size:17px; line-height:1.5; max-width: 65ch;">
                {{ __(':owners propriétaires identifiés contrôlent :sources des sources que GrimbaNews suit.', ['owners' => $totalOwners, 'sources' => $totalSources]) }}
                {{ __("La concentration éditoriale n'est pas un détail — quand un même groupe possède des outlets de différents biais, la diversité apparente du paysage médiatique cache une convergence d'intérêts au sommet.") }}
            </p>
            <p class="small opacity-65 mb-0" style="max-width:65ch;">
                {{ __("Données issues de sources publiques (registres d'entreprises, rapports annuels, AllSides, MBFC). Ouvrez une fiche source via le panneau du dessous pour voir le profil détaillé.") }}
            </p>
        </header>

        @if($owners->isEmpty())
            <div class="glass-panel p-4 text-center">
                <p class="mb-0">{{ __("Aucune donnée de propriété pour l'instant.") }}</p>
            </div>
        @else
            <ul class="list-unstyled m-0">
                @foreach($owners as $owner)
                    <li class="glass-panel p-3 p-md-4 mb-3">
                        <header class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div>
                                <h2 class="m-0 mb-1" style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-weight:600; font-size:24px; letter-spacing:-0.3px;">
                                    {{ $owner['name'] }}
                                </h2>
                                <div class="small opacity-75">
                                    {{ trans_choice(':count source suivie|:count sources suivies', count($owner['sources']), ['count' => count($owner['sources'])]) }} ·
                                    @php
                                        $byBias = ['left'=>0,'center'=>0,'right'=>0,'unknown'=>0];
                                        foreach ($owner['sources'] as $s) $byBias[$s->bias_rating ?? 'unknown']++;
                                        $known = $byBias['left'] + $byBias['center'] + $byBias['right'];
                                        $parts = [];
                                        foreach (['left','center','right'] as $b) if ($byBias[$b] > 0) $parts[] = $byBias[$b] . ' ' . mb_strtolower($biasMeta[$b]['label']);
                                    @endphp
                                    {{ $parts ? implode(' · ', $parts) : __('biais à classer') }}
                                </div>
                            </div>

                            @if($known >= 2)
                                <span style="
                                    display:inline-flex; padding:5px 12px; border-radius:9999px;
                                    background:rgba(192,57,43,0.10); color:#c0392b;
                                    font-family:'Public Sans',system-ui,sans-serif;
                                    font-size:12px; font-weight:700; letter-spacing:0.4px; text-transform:uppercase;
                                " title="{{ __('Possède des médias sur ≥2 côtés du spectre') }}">
                                    {{ __('Multi-biais') }}
                                </span>
                            @endif
                        </header>

                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:12px;">
                            @foreach($owner['sources'] as $s)
                                @php $b = $s->bias_rating ?? 'unknown'; $meta = $biasMeta[$b]; @endphp
                                <a href="{{ url('/sources/' . ($s->slug ?? '')) }}"
                                   style="
                                       display:flex; align-items:center; gap:12px;
                                       padding:10px 14px;
                                       border:1px solid rgba(26,23,19,0.10);
                                       border-left:4px solid {{ $meta['color'] }};
                                       border-radius:10px;
                                       background:rgba(255,255,255,0.55);
                                       color:var(--gn-ink,#1a1713);
                                       text-decoration:none;
                                   ">
                                    {!! Theme::partial('source-logo', [
                                        'source_id' => $s->id,
                                        'name'    => $s->name,
                                        'website' => $s->website ?? null,
                                        'logo_url' => $s->logo_url ?? null,
                                        'logo_status' => $s->logo_status ?? 'unknown',
                                        'logo_checked_at' => $s->logo_checked_at ?? null,
                                        'size'    => 34,
                                        'color'   => $meta['color'],
                                    ]) !!}
                                    <span style="flex:1; min-width:0;">
                                        <span style="display:block; font-weight:600; line-height:1.2; word-break:break-word;">
                                            {{ $s->name }}
                                        </span>
                                        <span class="small opacity-65" style="display:block;">
                                            {{ $s->country ?? '—' }} · {{ $meta['label'] }}
                                            @if($s->credibility_score)
                                                · {{ $s->credibility_score }}
                                            @endif
                                        </span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <p class="mt-4 small opacity-60 text-center">
            {{ __('Vous remarquez une erreur ou un propriétaire manquant ?') }}
            <a href="{{ url('/contact') }}" class="text-decoration-underline">{{ __('Signalez-le') }}</a>.
        </p>
    </div>
</section>
