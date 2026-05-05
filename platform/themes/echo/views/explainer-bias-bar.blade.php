@php
    Theme::layout('grimba-chrome');
@endphp

<section class="grimba-bias-bar-explainer py-5">
    <div class="container" style="max-width: 920px;">

        <header class="glass-panel p-4 p-md-5 mb-5">
            <span class="grimba-methodology__kicker">{{ __('Comprendre') }}</span>
            <h1 class="grimba-methodology__title mt-2 mb-3">
                {{ __('Le baromètre de couverture, segment par segment') }}
            </h1>
            <p class="lead opacity-85" style="font-size: 18px; line-height: 1.55;">
                {{ __("Sur chaque article et chaque dossier, GrimbaNews affiche un baromètre de couverture qui montre la part des sources de gauche, du centre et de droite ayant publié sur le sujet. C'est notre élément central : il existe pour qu'aucune histoire ne soit lue d'un seul côté du spectre sans que le lecteur le voie.") }}
            </p>
        </header>

        <article class="glass-panel p-4 p-md-5 mb-4">
            <h2 class="h4 mb-3">{{ __('Comment se lit le baromètre') }}</h2>
            <p class="mb-3">
                {{ __("Trois segments, dans cet ordre fixe : Gauche · Centre · Droite. La largeur de chaque segment correspond au pourcentage de sources de ce camp parmi les sources notées qui couvrent l'histoire. Au survol, le décompte brut apparaît (ex. « Gauche 3 · Centre 7 · Droite 2 (12 sources) »).") }}
            </p>

            <div class="row g-3 my-4">
                <div class="col-md-6">
                    <h3 class="h6 mb-2 opacity-75">{{ __('Couverture équilibrée') }}</h3>
                    <div style="display:flex;height:14px;border-radius:9999px;overflow:hidden;background:rgba(0,0,0,.08); margin-bottom:6px;">
                        <div style="width:33%;background:#3b82f6;"></div>
                        <div style="width:34%;background:#a8a8a8;"></div>
                        <div style="width:33%;background:#e84c3d;"></div>
                    </div>
                    <small class="opacity-65">{{ __('Gauche 33% · Centre 34% · Droite 33%') }}</small>
                </div>

                <div class="col-md-6">
                    <h3 class="h6 mb-2 opacity-75">{{ __('Couverture déséquilibrée') }}</h3>
                    <div style="display:flex;height:14px;border-radius:9999px;overflow:hidden;background:rgba(0,0,0,.08); margin-bottom:6px;">
                        <div style="width:8%;background:#3b82f6;"></div>
                        <div style="width:25%;background:#a8a8a8;"></div>
                        <div style="width:67%;background:#e84c3d;"></div>
                    </div>
                    <small class="opacity-65">{{ __('Gauche 8% · Centre 25% · Droite 67% — angle mort possible.') }}</small>
                </div>

                <div class="col-md-6">
                    <h3 class="h6 mb-2 opacity-75">{{ __('Une seule source') }}</h3>
                    <div style="display:flex;height:4px;border-radius:9999px;overflow:hidden;background:rgba(0,0,0,.06); position:relative; margin-bottom:6px;">
                        <div style="width:100%;background:#3b82f6;"></div>
                        <div style="position:absolute;inset:0;background:repeating-linear-gradient(45deg,transparent 0,transparent 4px,rgba(26,23,19,.05) 4px,rgba(26,23,19,.05) 8px);pointer-events:none;"></div>
                    </div>
                    <small class="opacity-65">{{ __("Bar plein avec hachuré pour signaler que la distribution n'est pas représentative.") }}</small>
                </div>

                <div class="col-md-6">
                    <h3 class="h6 mb-2 opacity-75">{{ __('Source non classée') }}</h3>
                    <p class="small opacity-65 mb-0">
                        {{ __('Pas de baromètre rendu : les sources sans rating identifié restent affichées dans la liste avec une étiquette « Non évalué ».') }}
                    </p>
                </div>
            </div>
        </article>

        <article class="glass-panel p-4 p-md-5 mb-4">
            <h2 class="h4 mb-3">{{ __('Convention francophone') }}</h2>
            <p class="mb-3">
                {{ __("Nous gardons la couleur bleue pour la gauche et la couleur rouge pour la droite, sur toutes les éditions (Afrique, International). Cette convention est l'inverse de celle d'autres lecteurs internationaux comme Ground.news, qui inverse les couleurs aux États-Unis pour suivre la convention partisane américaine. Notre choix : un seul code couleur stable pour l'ensemble du site, francophone par défaut, indiqué clairement.") }}
            </p>
            <p class="opacity-75 small mb-3">
                {{ __("Si vous lisez en anglais ou si vous arrivez d'un pays où la convention est inversée, le label sous chaque segment (« Gauche / Centre / Droite ») désambigüise.") }}
            </p>

            {{-- S344 — opt-in toggle for the US/anglo convention. --}}
            <div class="d-flex gap-2 flex-wrap align-items-center" style="border-top: 1px dashed rgba(0,0,0,0.10); padding-top: 12px; margin-top: 6px;">
                <span class="small opacity-75">{{ __('Vous préférez la convention US (rouge=gauche, bleu=droite) ?') }}</span>
                <a href="?fr_convention=0" class="btn-grimba btn-grimba--ghost btn-grimba--sm" data-grimba-flip="us">
                    {{ __('Adopter la convention US') }}
                </a>
                <a href="?fr_convention=1" class="btn-grimba btn-grimba--ghost btn-grimba--sm" data-grimba-flip="fr">
                    {{ __('Revenir à la convention FR') }}
                </a>
                <span class="small opacity-55" style="font-size: 11.5px;">
                    {{ __('Le choix est stocké dans votre navigateur — aucun compte requis.') }}
                </span>
            </div>
        </article>

        <article class="glass-panel p-4 p-md-5 mb-4">
            <h2 class="h4 mb-3">{{ __("D'où viennent les classements") }}</h2>
            <p class="mb-2">
                {{ __("Les biais éditoriaux ne sont pas évalués par GrimbaNews seul. Nous combinons :") }}
            </p>
            <ul class="ps-3 mb-3">
                <li class="mb-1">{{ __('AllSides — comité de lecteurs avec sondage à l\'aveugle') }}</li>
                <li class="mb-1">{{ __('Ad Fontes Media — analyse linguistique + comité de relecture') }}</li>
                <li class="mb-1">{{ __('Media Bias / Fact Check — évaluation indépendante par revue éditoriale') }}</li>
                <li class="mb-1">{{ __('Notre comité éditorial GrimbaNews — calage francophone et africain (sources hors couverture des trois agences anglo-saxonnes)') }}</li>
            </ul>
            <p class="small opacity-75 mb-0">
                {{ __('La cote de chaque source est la moyenne pondérée de ces quatre signaux. Le score brut est consultable sur la page de chaque source.') }}
            </p>
        </article>

        <article class="glass-panel p-4 p-md-5 mb-4">
            <h2 class="h4 mb-3">{{ __('Cliquer pour filtrer') }}</h2>
            <p class="mb-0">
                {{ __("Sur les pages de dossier, chaque segment est cliquable : il filtre la liste des articles à ce camp uniquement. Pratique pour comparer le cadrage côte à côte sans changer de page.") }}
            </p>
        </article>

        <div class="d-flex gap-2 flex-wrap mt-4">
            <a href="{{ url('/methodologie') }}" class="btn-grimba btn-grimba--solid btn-grimba--sm">
                {{ __('Voir la méthodologie complète') }} →
            </a>
            <a href="{{ url('/sources') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                {{ __('Voir toutes les sources classées') }}
            </a>
            <a href="{{ url('/contact') }}?topic=methodo" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                {{ __('Contester un classement') }}
            </a>
        </div>

    </div>
</section>
