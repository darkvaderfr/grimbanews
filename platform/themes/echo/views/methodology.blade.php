@php
    Theme::layout('grimba-chrome');
    // S350 — page-specific OG image.
    Theme::set('grimba_og_image', url('/og/page?' . http_build_query([
        'kicker' => 'Méthodologie',
        'title'  => 'Comment GrimbaNews classe l\'information',
    ])));
@endphp

<section class="grimba-methodology py-5">
    <div class="container">

        <header class="grimba-methodology__hero glass-panel grimba-editorial-ribbon p-4 p-md-5 mb-5">
            {{-- Wave LLLLLLLLL (Vader 2026-05-22) — hero strings were
                hardcoded FR; EN readers landing on /methodologie?lang=en
                saw FR despite the locale-enforce middleware delivering
                the correct locale. Now wrapped in __() with EN
                translations in lang/en.json. --}}
            <span class="grimba-methodology__kicker">{{ __('Méthodologie') }}</span>
            <h1 class="grimba-methodology__title">{{ __("Comment GrimbaNews classe l'information") }}</h1>
            <p class="grimba-methodology__lede">
                {{ __("Nous évaluons le biais éditorial des sources, repérons les angles morts, notons la crédibilité et gouvernons la liste des médias suivis. La méthode reste ouverte, révisable et contestable.") }}
            </p>
            {{-- Wave WWWWWWWWW (Vader 2026-05-22) — section bodies were
                hardcoded FR. EN readers landing on /methodologie?lang=en
                saw FR despite the locale-enforce middleware delivering
                the correct locale. All section H2 + body strings now
                wrapped in __() with EN translations in lang/en.json. --}}
            <div class="d-flex gap-2 flex-wrap mt-3">
                <a href="{{ url('/sources') }}" class="btn-grimba btn-grimba--solid btn-grimba--sm">{{ __('Voir les sources classées') }}</a>
                <a href="{{ url('/contact') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">{{ __('Contester un classement') }}</a>
            </div>
        </header>

        <article class="grimba-methodology__section">
            <h2>{{ __('1. Notre engagement') }}</h2>
            <p>
                {!! __("GrimbaNews est un média francophone indépendant édité par Iboga Ventures. Notre objectif : <strong>permettre à nos lectrices et lecteurs de voir chaque angle de chaque histoire</strong>. Nous ne prétendons pas à la neutralité — nous la rendons visible, quantifiable et contestable.") !!}
            </p>
            <p>
                {{ __('Trois principes encadrent notre travail :') }}
            </p>
            <ul>
                <li>{!! __("<strong>Transparence</strong> — chaque classement est public, chaque source dispose d'une fiche avec son score, son orientation et son type de propriété.") !!}</li>
                <li>{!! __("<strong>Pluralisme</strong> — les angles gauche, centre et droite sont toujours représentés quand ils existent. Quand ils manquent, nous le signalons (angle mort).") !!}</li>
                <li>{!! __("<strong>Révision continue</strong> — nos classements évoluent. Un média peut monter ou descendre dans la grille selon ses pratiques.") !!}</li>
            </ul>
        </article>

        <article class="grimba-methodology__section">
            <h2>{{ __('2. Comment nous classons les biais') }}</h2>
            <p>
                {!! __('Chaque source suivie est placée sur un axe simple à trois positions :') !!}
                <span class="grimba-coverage__dot grimba-coverage__dot--left"></span> <strong>{{ __('Gauche') }}</strong>,
                <span class="grimba-coverage__dot grimba-coverage__dot--center"></span> <strong>{{ __('Centre') }}</strong>,
                <span class="grimba-coverage__dot grimba-coverage__dot--right"></span> <strong>{{ __('Droite') }}</strong>.
                {{ __('Nous nous appuyons sur quatre signaux :') }}
            </p>
            <ol>
                <li>{!! __("<strong>Choix éditorial des sujets</strong> — quelles histoires sont couvertes, quelles histoires sont absentes du fil, sur une fenêtre glissante de trente jours.") !!}</li>
                <li>{!! __("<strong>Cadrage linguistique</strong> — analyse lexicale des titres et des chapôs (vocabulaire, connotations, champ sémantique), comparée à un corpus de référence.") !!}</li>
                <li>{!! __("<strong>Structure de l'argumentation</strong> — dans les éditoriaux et les tribunes, rapport entre sources citées à gauche, au centre et à droite.") !!}</li>
                <li>{!! __("<strong>Validation croisée externe</strong> — nos classements sont calibrés sur les méthodologies de AllSides et Media Bias/Fact Check, adaptées au paysage médiatique francophone.") !!}</li>
            </ol>
            <p>
                {!! __("Un classement n'est <em>jamais</em> un jugement sur l'exactitude d'un article. Une source de gauche peut être très crédible ; une source de centre peut être faiblement crédible. Ce sont deux dimensions distinctes.") !!}
            </p>
        </article>

        <article class="grimba-methodology__section">
            <h2>{{ __("3. Qu'est-ce qu'un angle mort ?") }}</h2>
            <p>
                {!! __("Un <strong>angle mort</strong> est une histoire importante que seul un côté du spectre politique couvre. Par exemple, un sujet dont 95 % des articles proviennent de sources classées à gauche sera signalé comme <em>angle mort de gauche</em>. Il ne s'agit pas d'un jugement sur la véracité de l'histoire, mais d'un signal éditorial : cette histoire existe, et votre fil habituel ne la traite peut-être pas.") !!}
            </p>
            <p>
                {{ __("GrimbaNews calcule chaque jour les angles morts à partir des clusters d'histoires regroupées par notre moteur. Un cluster est marqué comme angle mort quand la couverture dépasse 80 % d'un seul camp sur au moins six sources distinctes.") }}
            </p>
        </article>

        <article class="grimba-methodology__section">
            <h2>{{ __('4. Score de crédibilité (0–100)') }}</h2>
            <p>
                {{ __('Le score de crédibilité combine quatre indicateurs, chacun noté sur 25 :') }}
            </p>
            <ul>
                <li>{!! __("<strong>Fact-checking</strong> — taux d'articles corrigés ou rétractés sur les douze derniers mois.") !!}</li>
                <li>{!! __("<strong>Sourcing</strong> — part d'articles explicitement sourcés (noms, documents, données).") !!}</li>
                <li>{!! __("<strong>Séparation info / opinion</strong> — distinction claire entre dépêches, enquêtes, analyses et éditoriaux.") !!}</li>
                <li>{!! __("<strong>Historique éditorial</strong> — continuité de la ligne, ancienneté, récompenses journalistiques.") !!}</li>
            </ul>
            <p>
                {!! __('Un score ≥ 85 est affiché en <span style="color:#22c55e;font-weight:600;">vert</span> ; de 70 à 84 en <span style="color:#eab308;font-weight:600;">ambre</span> ; en dessous de 70 en <span style="color:#ef4444;font-weight:600;">rouge</span>. Le score ne remplace pas le jugement du lecteur — il l\'informe.') !!}
            </p>
        </article>

        <article class="grimba-methodology__section">
            <h2>{{ __('5. Type de propriété') }}</h2>
            <p>
                {{ __("Savoir qui possède un média éclaire ce qu'on lit. Nous classons chaque source suivant quatre catégories :") }}
            </p>
            <ul>
                <li>{!! __("<strong>Indépendant</strong> — capital détenu par la rédaction ou des actionnaires sans autre activité économique significative.") !!}</li>
                <li>{!! __("<strong>Privé</strong> — détenu par un groupe industriel ou financier (presse « d'intérêts »).") !!}</li>
                <li>{!! __("<strong>État</strong> — financé par l'argent public, rédaction plus ou moins autonome selon les pays.") !!}</li>
                <li>{!! __("<strong>Associatif</strong> — structure à but non lucratif.") !!}</li>
            </ul>
        </article>

        <article class="grimba-methodology__section" id="contestation">
            <h2>{{ __('6. Contester un classement') }}</h2>
            <p>
                {!! __('Vous pensez qu\'une source est mal classée ? Vous avez raison de poser la question. Écrivez-nous à <a href="mailto:methodologie@grimbanews.com">methodologie@grimbanews.com</a> avec votre argumentation et vos exemples concrets (titres, dates, captures d\'écran). Chaque contestation est examinée par notre comité éditorial sous quinze jours et la réponse est publique.') !!}
            </p>
            <p>
                {!! __('Le <a href=":url">journal des révisions</a> trace chaque changement de classement avec sa date et sa motivation.', ['url' => url('/sources')]) !!}
            </p>
        </article>

        <article class="grimba-methodology__section">
            <h2>{{ __('7. Gouvernance et financement') }}</h2>
            <p>
                {{ __("GrimbaNews est édité par Iboga Ventures. Nous n'acceptons aucun financement publicitaire de partis politiques, gouvernements ou groupes de presse dont nous classons le biais. Nos revenus proviennent exclusivement des abonnements et du mécénat associatif.") }}
            </p>
            <p>
                {{ __("La direction éditoriale est indépendante de la direction générale. Aucun contenu ne peut être retiré à la demande d'un actionnaire.") }}
            </p>
        </article>

        {{-- S313 — chip reference. Every chip rendered alongside its
              definition so readers can decode any chip they see anywhere
              else on the site. --}}
        <article class="glass-panel p-4 p-md-5 mb-4">
            <h2 class="h4 mb-3">{{ __('Référence des étiquettes') }}</h2>
            <p class="mb-3 opacity-85">
                {{ __("Chaque source affiche jusqu'à trois étiquettes : son biais éditorial sur 7 niveaux, sa fiabilité sur 5 niveaux, son type de propriétaire sur 8 catégories. Voici à quoi elles ressemblent et ce qu'elles signifient.") }}
            </p>

            <h3 class="h6 mt-4 mb-3 text-uppercase opacity-65">{{ __('Biais éditorial') }}</h3>
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach(['far_left','left','lean_left','center','lean_right','right','far_right'] as $tier)
                    {!! Theme::partial('bias-chip', ['tier' => $tier, 'size' => 'md']) !!}
                @endforeach
            </div>
            <p class="small opacity-75 mb-0">
                {{ __("Moyenne pondérée des quatre signaux (AllSides + Ad Fontes + MBFC + comité GrimbaNews). Le baromètre de couverture sur chaque dossier compresse ces 7 niveaux en 3 camps (gauche / centre / droite) pour rester lisible.") }}
                <a href="{{ url('/comprendre-le-barometre') }}" class="text-decoration-underline">
                    {{ __('Comment lire le baromètre →') }}
                </a>
            </p>

            <h3 class="h6 mt-4 mb-3 text-uppercase opacity-65">{{ __('Fiabilité éditoriale') }}</h3>
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach(['very_low','low','mixed','high','very_high'] as $tier)
                    {!! Theme::partial('factuality-chip', ['tier' => $tier, 'size' => 'md']) !!}
                @endforeach
            </div>
            <p class="small opacity-75 mb-0">
                {{ __("Combinaison de la qualité du sourcing, de la rapidité des corrections, du sensationnalisme du langage et du respect du contexte.") }}
            </p>

            <h3 class="h6 mt-4 mb-3 text-uppercase opacity-65">{{ __('Propriété') }}</h3>
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach(['independent','government','conglomerate','private_equity','individual','telecom','corporation'] as $cat)
                    {!! Theme::partial('ownership-chip', ['category' => $cat, 'size' => 'md']) !!}
                @endforeach
            </div>
            <p class="small opacity-75 mb-0">
                {{ __("Catégorie d'actionnaire principal. Mise à jour au cas par cas par le comité éditorial.") }}
            </p>
        </article>

        <article class="grimba-methodology__section" id="language-detection">
            <h2>{{ __("Détection automatique de la langue") }}</h2>
            <p>
                {{ __("Chaque article ingéré par GrimbaNews est tagué avec sa langue d'origine (français ou anglais). Le détecteur combine quatre signaux, dans cet ordre — premier qui gagne :") }}
            </p>
            <ol>
                <li><strong>{{ __("Indication directe du fournisseur") }}</strong> — {{ __("certains flux RSS et API déclarent eux-mêmes la langue de chaque article.") }}</li>
                <li><strong>{{ __("Langue de la source") }}</strong> — {{ __("la fiche source (rédaction, pays, ligne éditoriale) porte une langue déclarée par l'équipe.") }}</li>
                <li><strong>{{ __("TLD du domaine") }}</strong> — {{ __("les domaines en .fr, .qc.ca, .sn, .ci, .ml, .ne, .bf, .cm, .dz, .ma, .tn (et 13 autres) sont francophones par défaut ; .uk, .au, .nz, .ie, .za, .ng, .ke, .gh, .in (et 5 autres) sont anglophones.") }}</li>
                <li><strong>{{ __("Empreinte linguistique du texte") }}</strong> — {{ __("analyse des 800 premiers caractères du titre + description, 19 marqueurs français vs. 17 marqueurs anglais. Le verdict ne s'applique qu'avec une confiance ≥ 75 %.") }}</li>
            </ol>
            <p>
                {{ __("Si aucun signal n'est confiant, l'article reste 'non classifié' jusqu'au prochain balayage nocturne. Vous verrez un badge ambre 'Langue non classifiée' sur ces articles — c'est un état transitoire, pas un défaut de l'article.") }}
            </p>
            <p>
                {{ __("Une fois classés, les articles français sont automatiquement traduits en anglais (et vice versa) par NobuAI ; le lecteur reçoit la version qui correspond à sa préférence de langue.") }}
            </p>
        </article>

        <footer class="grimba-methodology__footer glass-panel p-4 mt-4">
            <p class="mb-2">{!! __('<strong>Version 1.0</strong> — publiée le :date.', ['date' => now()->locale(app()->getLocale())->isoFormat('D MMMM YYYY')]) !!}</p>
            <p class="small mb-0 opacity-75">
                {{ __("Cette méthodologie évolue. Les mises à jour sont datées et archivées. Votre retour nous aide à l'améliorer.") }}
            </p>
        </footer>

    </div>
</section>
