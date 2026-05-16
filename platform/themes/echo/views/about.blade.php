@php
    Theme::layout('grimba-chrome');
    // S350 — page-specific OG image.
    Theme::set('grimba_og_image', url('/og/page?' . http_build_query([
        'kicker' => 'À propos',
        'title'  => 'GrimbaNews — chaque histoire, lue depuis tous les côtés',
    ])));
@endphp

<section class="grimba-about py-5">
    <div class="container" style="max-width: 920px;">

        <header class="glass-panel grimba-editorial-ribbon p-4 p-md-5 mb-5">
            <span class="grimba-methodology__kicker">{{ __('À propos') }}</span>
            <h1 class="grimba-methodology__title mt-2 mb-3">
                {{ __('GrimbaNews') }}
            </h1>
            <p class="lead opacity-90" style="font-size: 19px; line-height: 1.55;">
                {{ __("Une plateforme francophone qui rend visible le biais éditorial et les angles morts de l'actualité. Chaque histoire, lue depuis tous les côtés du spectre.") }}
            </p>
        </header>

        <article class="glass-panel p-4 p-md-5 mb-4">
            <h2 class="h4 mb-3">{{ __('Pourquoi') }}</h2>
            <p class="mb-3 opacity-90">
                {{ __("La plupart des lecteurs francophones n'ont pas accès à un outil qui montre, sur la même histoire, ce que la gauche, le centre et la droite écrivent. Les agrégateurs anglo-saxons existent mais sont biaisés par leur grille de référence états-unienne. La presse francophone est ce qu'elle est. La presse africaine francophone est sous-représentée partout.") }}
            </p>
            <p class="mb-3 opacity-90">
                {{ __("GrimbaNews cible cette absence : un seul outil, francophone, qui agrège ce qui s'écrit, étiquette le biais éditorial et la fiabilité de chaque source, et signale clairement les angles morts. Pas un autre flux d'actu — un cadre de lecture.") }}
            </p>
            <p class="mb-0 opacity-90">
                {{ __("Avec un focus assumé sur les éditions Afrique et International — parce que c'est précisément là que les outils existants laissent les lecteurs francophones avec les pires angles morts.") }}
            </p>
        </article>

        <article class="glass-panel p-4 p-md-5 mb-4">
            <h2 class="h4 mb-3">{{ __('Ce que nous faisons concrètement') }}</h2>
            <ul class="ps-3 mb-0">
                <li class="mb-2">{{ __("On agrège l'actualité depuis des centaines de sources francophones et internationales, classées sur 7 niveaux de biais éditorial et 5 niveaux de fiabilité.") }}</li>
                <li class="mb-2">{{ __('On regroupe les articles couvrant le même événement en dossiers, et on affiche un baromètre montrant la répartition gauche / centre / droite des sources qui le couvrent.') }}</li>
                <li class="mb-2">{{ __("On signale les angles morts — les histoires qu'un seul camp couvre — sur une page dédiée.") }}</li>
                <li class="mb-2">{{ __("On donne au lecteur les outils pour comparer le cadrage : sélectionner deux ou trois sources et voir leurs titres côte à côte sur la même histoire.") }}</li>
                <li class="mb-2">{{ __('On documente la propriété de chaque source — gouvernementale, conglomérat, individu, télécom, indépendante…') }}</li>
                <li class="mb-2">{{ __("On laisse le lecteur sauvegarder, suivre des sujets, et lire sans inscription. Aucune donnée personnelle n'est requise pour l'usage de base.") }}</li>
            </ul>
        </article>

        <article class="glass-panel p-4 p-md-5 mb-4">
            <h2 class="h4 mb-3">{{ __('Notre éthique') }}</h2>
            <ul class="ps-3 mb-0">
                <li class="mb-2"><strong>{{ __('Transparence') }}.</strong> {{ __("Tous nos classements sont publics, datés, contestables. La méthodologie est documentée page par page.") }}</li>
                <li class="mb-2"><strong>{{ __('Indépendance') }}.</strong> {{ __("Nous n'acceptons pas de financement publicitaire de partis politiques, de gouvernements ou de groupes de presse dont nous classons le biais. Nos revenus proviennent exclusivement des abonnements et du mécénat associatif.") }}</li>
                <li class="mb-2"><strong>{{ __('NobuAI assistée, pas autonome') }}.</strong> {{ __("Nos synthèses multi-sources sont assistées par NobuAI mais relues par notre comité éditorial. Aucun résumé n'est publié sans bouclage humain.") }}</li>
                <li class="mb-2"><strong>{{ __('Vie privée') }}.</strong> {{ __("Pas de tracker tiers. Les préférences de lecture (sujets suivis, articles sauvegardés, langue, thème) sont stockées dans des cookies de votre navigateur, pas sur nos serveurs.") }}</li>
            </ul>
        </article>

        <article class="glass-panel p-4 p-md-5 mb-4">
            <h2 class="h4 mb-3">{{ __('Comment lire le site') }}</h2>
            <p class="mb-2 opacity-90">{{ __("Trois pages valent le détour avant de plonger dans le flux :") }}</p>
            <div class="d-flex gap-2 flex-wrap mt-3">
                <a href="{{ url('/comprendre-le-barometre') }}" class="btn-grimba btn-grimba--solid btn-grimba--sm">
                    {{ __('Comprendre le baromètre') }} →
                </a>
                <a href="{{ url('/methodologie') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                    {{ __('Méthodologie complète') }}
                </a>
                <a href="{{ url('/sources') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                    {{ __('Toutes les sources classées') }}
                </a>
                <a href="{{ url('/faq') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                    {{ __('FAQ') }}
                </a>
            </div>
        </article>

    </div>
</section>
