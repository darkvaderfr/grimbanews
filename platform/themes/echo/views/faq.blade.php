@php
    Theme::layout('grimba-chrome');
    // S350 — page-specific OG image.
    Theme::set('grimba_og_image', url('/og/page?' . http_build_query([
        'kicker' => 'Foire aux questions',
        'title'  => 'Comment marche GrimbaNews ?',
    ])));

    $sections = [
        [
            'title' => __('Méthodologie'),
            'items' => [
                [
                    'q' => __("Comment GrimbaNews classe-t-il le biais d'une source ?"),
                    'a' => __("Nous combinons quatre signaux : AllSides (comité de lecteurs avec sondage à l'aveugle), Ad Fontes Media (analyse linguistique + revue), Media Bias / Fact Check (revue éditoriale indépendante) et notre propre comité éditorial pour les sources francophones et africaines hors couverture des trois agences anglo-saxonnes. Le résultat est un score sur sept niveaux : Extrême gauche, Gauche, Centre gauche, Centre, Centre droit, Droite, Extrême droite."),
                ],
                [
                    'q' => __("Comment fonctionne le baromètre de couverture ?"),
                    'a' => __("Sur chaque dossier, nous comptons les sources qui le couvrent et calculons la part de chaque camp (gauche / centre / droite). Le baromètre montre cette répartition en trois segments. Plus un côté domine, plus il est probable que l'autre côté ait un angle mort sur le sujet."),
                ],
                [
                    'q' => __("Que veut dire « angle mort » sur GrimbaNews ?"),
                    'a' => __("Un angle mort est une histoire importante rapportée presque exclusivement par un seul camp politique. Si la quasi-totalité des sources couvrant un sujet penchent à droite, les lecteurs qui ne lisent que la gauche ne savent pas que ce sujet existe — c'est leur angle mort. Notre page /angles-morts liste ces histoires avec deux onglets : « Pour la gauche » et « Pour la droite »."),
                ],
            ],
        ],
        [
            'title' => __('Biais et étiquettes'),
            'items' => [
                [
                    'q' => __("Pourquoi gardez-vous le bleu pour la gauche et le rouge pour la droite ?"),
                    'a' => __("C'est la convention francophone et continentale. Aux États-Unis, la convention est inversée — c'est ce qui crée la confusion sur des outils anglo-saxons. Nous avons choisi un seul code couleur stable, francophone par défaut, pour toutes les éditions du site, et nous le documentons sur la page « Comprendre le baromètre »."),
                ],
                [
                    'q' => __("Est-ce que le score de fiabilité est le même chose que le biais ?"),
                    'a' => __("Non. Une source peut être très fiable et avoir un biais marqué — par exemple une source de qualité éditoriale haute mais clairement à gauche. La fiabilité mesure la qualité du sourcing, la rapidité des corrections et le respect du contexte. Le biais mesure l'orientation politique. On affiche les deux séparément pour que le lecteur fasse son propre tri."),
                ],
                [
                    'q' => __("Comment contester un classement ?"),
                    'a' => __("Chaque page de source a un lien « Contester un classement » qui ouvre notre formulaire de contact. Nous publions toutes les contestations recevables et leur statut sur une page d'audit ouverte (en construction)."),
                ],
            ],
        ],
        [
            'title' => __('NobuAI'),
            'items' => [
                [
                    'q' => __("Qu'est-ce que NobuAI ?"),
                    'a' => __("NobuAI est notre assistant éditorial. Il aide à générer les synthèses multi-sources et les comparaisons de cadrage. Il est toujours assisté par un comité éditorial humain qui valide avant publication — aucun contenu n'est publié sans bouclage humain."),
                ],
                [
                    'q' => __("Est-ce que NobuAI invente des informations ?"),
                    'a' => __("Non. Les synthèses sont extractives : NobuAI résume des phrases et des paragraphes déjà présents dans les articles sources. Aucun fait n'est synthétisé sans citation possible. Si vous voyez une affirmation dans une synthèse qui ne vous semble pas attribuable à une source, signalez-la — nous corrigeons publiquement."),
                ],
            ],
        ],
        [
            'title' => __('Données et vie privée'),
            'items' => [
                [
                    'q' => __("Avez-vous besoin que je crée un compte ?"),
                    'a' => __("Non, pas pour la lecture. Vos préférences (sujets suivis, articles sauvegardés, langue, thème, édition) sont stockées dans des cookies de votre navigateur. Vous pouvez vider vos cookies pour tout effacer."),
                ],
                [
                    'q' => __("Que faites-vous avec mes données ?"),
                    'a' => __("Pas grand-chose. Pas de tracker tiers. Pas de cookie marketing. Pas de partage. Pour la mesure d'audience, nous utilisons des compteurs anonymes côté serveur. Le détail est dans notre politique de confidentialité."),
                ],
                [
                    'q' => __("Comment résilier la newsletter ?"),
                    'a' => __("Chaque email contient un lien de désabonnement. Le clic est immédiatement pris en compte, sans email de confirmation supplémentaire."),
                ],
            ],
        ],
        [
            'title' => __('Abonnement et accès'),
            'items' => [
                [
                    'q' => __("Y a-t-il un paywall ?"),
                    'a' => __("La lecture du site est gratuite — homepage, liste des sources, dossiers, angles morts, page /coffre. Le tier payant ouvre la lecture du texte complet de chaque article (au lieu de l'extrait + lien externe), retire les emplacements publicitaires, et débloque les newsletters quotidienne et thématiques. Les angles morts ne sont JAMAIS paywallés."),
                ],
                [
                    'q' => __("Comment soutenir le projet sans payer un abonnement ?"),
                    'a' => __("Partagez les articles que vous lisez ici. Contestez nos classements quand vous trouvez qu'on s'est trompé. Suggérez des sources francophones et africaines qu'on devrait suivre. Le projet vit autant de retours éditoriaux que de revenus."),
                ],
            ],
        ],
    ];

    // Wave GGGGGGG — FAQPage JSON-LD. Google can surface FAQ rich
    // results in SERP when the page declares the canonical FAQPage
    // schema with Question + acceptedAnswer entities. We build this
    // from the same $sections array so copy stays single-sourced.
    $__faqJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($sections)->flatMap(fn ($s) => $s['items'])->map(fn ($item) => [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $item['a'],
            ],
        ])->values()->all(),
    ];
    Theme::set('grimbaJsonLd', json_encode($__faqJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
@endphp

<section class="grimba-faq py-5">
    <div class="container" style="max-width: 920px;">

        <header class="glass-panel grimba-editorial-ribbon p-4 p-md-5 mb-5">
            <span class="grimba-methodology__kicker">{{ __('Foire aux questions') }}</span>
            <h1 class="grimba-methodology__title mt-2 mb-3">
                {{ __('Comment marche GrimbaNews ?') }}
            </h1>
            <p class="lead opacity-90" style="font-size: 18px; line-height: 1.55;">
                {{ __("Méthodologie, NobuAI, vie privée, abonnement. Si une question n'est pas couverte, écrivez-nous et nous l'ajoutons.") }}
            </p>
        </header>

        @foreach($sections as $sectionIndex => $section)
            <section class="glass-panel p-4 p-md-5 mb-4">
                <h2 class="h4 mb-3">{{ $section['title'] }}</h2>

                @foreach($section['items'] as $itemIndex => $item)
                    @php $anchor = 'q-' . $sectionIndex . '-' . $itemIndex; @endphp
                    <details id="{{ $anchor }}" class="grimba-faq-item" {{ ($sectionIndex === 0 && $itemIndex === 0) ? 'open' : '' }}>
                        <summary style="
                            cursor: pointer;
                            padding: 14px 0;
                            border-bottom: 1px dashed rgba(26, 23, 19, 0.10);
                            font-family: 'Public Sans', system-ui, sans-serif;
                            font-weight: 600;
                            font-size: 15.5px;
                            list-style: none;
                            display: flex;
                            align-items: center;
                            gap: 10px;
                        ">
                            <span style="
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                width: 22px; height: 22px;
                                border-radius: 50%;
                                background: rgba(26, 23, 19, 0.08);
                                font-size: 13px;
                                flex-shrink: 0;
                                transition: transform 0.15s ease;
                            " class="grimba-faq-toggle">+</span>
                            <span>{{ $item['q'] }}</span>
                        </summary>
                        <div style="padding: 12px 0 16px 32px; line-height: 1.6; color: var(--gn-ink, #1a1713); opacity: 0.92;">
                            {{ $item['a'] }}
                        </div>
                    </details>
                @endforeach
            </section>
        @endforeach

        <div class="d-flex gap-2 flex-wrap mt-4">
            <a href="{{ url('/methodologie') }}" class="btn-grimba btn-grimba--solid btn-grimba--sm">
                {{ __('Voir la méthodologie complète') }}
            </a>
            <a href="{{ url('/contact') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                {{ __('Poser une autre question') }}
            </a>
        </div>

    </div>
</section>

<style>
    .grimba-faq-item summary::-webkit-details-marker { display: none; }
    .grimba-faq-item[open] .grimba-faq-toggle { transform: rotate(45deg); }
</style>
