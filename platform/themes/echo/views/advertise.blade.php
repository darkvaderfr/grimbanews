@extends(Theme::getThemeNamespace('layouts.grimba-chrome'))

@section('content')
    @php
        $slot = trim((string) ($slot ?? ''));
        $email = trim((string) config('grimba_ads.sales_email', 'ads@grimbanews.com'));
        $subject = rawurlencode('Sponsor GrimbaNews' . ($slot !== '' ? ' — ' . $slot : ''));
        $mailto = 'mailto:' . $email . '?subject=' . $subject;

        $packs = [
            [
                'tier' => __('Discovery'),
                'price' => '$1,200',
                'period' => __('par mois'),
                'features' => [
                    __('1 emplacement (home top OU article top)'),
                    __('Ciblage par édition'),
                    __('Reporting hebdomadaire'),
                    __('Création standard incluse'),
                ],
            ],
            [
                'tier' => __('Focus'),
                'price' => '$3,800',
                'period' => __('par mois'),
                'features' => [
                    __('3 emplacements premium'),
                    __('Ciblage région + édition + sujet'),
                    __('Reporting quotidien'),
                    __('Création + A/B testing'),
                    __('Slot natif in-feed'),
                ],
                'featured' => true,
            ],
            [
                'tier' => __('Atlas'),
                'price' => __('Sur mesure'),
                'period' => __('contrat annuel'),
                'features' => [
                    __('Tous les emplacements'),
                    __('Inventaire dossier exclusif'),
                    __('API + intégration NobuAI'),
                    __('Compte dédié + KPIs sur mesure'),
                    __('Livraison de campagne ≤ 24h'),
                ],
            ],
        ];

        $featureRows = [
            ['icon' => '◇', 'title' => __('Auditoire éditorialement informé'), 'body' => __("Nos lecteurs comparent activement les biais entre sources. Vos messages atterrissent dans un contexte de confiance, pas de scroll passif.")],
            ['icon' => '◈', 'title' => __('Ciblage par édition régionale'), 'body' => __('Quatre éditions (Afrique, Europe, Amériques, International) plus catégories éditoriales. Choisissez votre audience à la granularité d\'une rubrique.')],
            ['icon' => '◉', 'title' => __('Telemetry NobuAI'), 'body' => __('Impressions, clics, taux de complétion, et signal d\'attention propriétaire — exposé en temps réel dans votre tableau de bord.')],
            ['icon' => '◬', 'title' => __('Diffusion auto + revue éditoriale'), 'body' => __('Vos créations sont validées par notre équipe sous 24h. Pas de surprise éditoriale, pas de placement à côté d\'un sujet sensible non souhaité.')],
        ];
    @endphp

    <style>
        .grimba-ads-page {
            max-width: 1180px;
            margin-inline: auto;
            padding: clamp(20px, 4vw, 56px) clamp(16px, 3vw, 32px) 80px;
            color: var(--gn-ink, #1a1713);
        }

        .grimba-ads-page__hero {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
            gap: clamp(28px, 5vw, 64px);
            align-items: end;
            padding: clamp(28px, 5vw, 56px) clamp(28px, 5vw, 48px);
            border-radius: 26px;
            background:
                radial-gradient(120% 80% at 0% 0%, rgba(192, 57, 43, .14), transparent 55%),
                radial-gradient(80% 60% at 100% 100%, rgba(59, 130, 246, .14), transparent 60%),
                linear-gradient(180deg, rgba(255, 255, 255, .82), rgba(255, 255, 255, .58));
            border: 1px solid rgba(26, 23, 19, .08);
            box-shadow: 0 28px 80px rgba(26, 23, 19, .10);
        }

        .grimba-ads-page__kicker {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: linear-gradient(135deg, #1a1713, #3a342c);
            color: #f6f1e8;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            box-shadow: 0 6px 18px rgba(26, 23, 19, .22);
        }

        .grimba-ads-page__kicker-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #f6f1e8;
        }

        .grimba-ads-page__hero h1 {
            margin: 14px 0 16px;
            font-family: 'Fraunces', 'Playfair Display', Georgia, serif;
            font-weight: 800;
            font-size: clamp(36px, 5.4vw, 64px);
            line-height: 1.02;
            letter-spacing: -0.025em;
            color: var(--gn-ink, #1a1713);
        }

        .grimba-ads-page__lede {
            margin: 0 0 24px;
            font-size: clamp(17px, 1.6vw, 20px);
            line-height: 1.5;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .68));
        }

        .grimba-ads-page__ctas {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .grimba-ads-page__cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 48px;
            padding: 0 22px;
            border-radius: 999px;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: .02em;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .grimba-ads-page__cta--primary {
            background: #14110d;
            color: #fffaf1;
            box-shadow: 0 14px 36px rgba(20, 17, 13, .28);
        }

        .grimba-ads-page__cta--primary:hover,
        .grimba-ads-page__cta--primary:focus-visible {
            background: #1a1713;
            color: #fffaf1;
            transform: translateY(-1px);
            box-shadow: 0 18px 44px rgba(20, 17, 13, .34);
        }

        .grimba-ads-page__cta--ghost {
            background: rgba(255, 255, 255, .68);
            color: var(--gn-ink, #1a1713);
            border: 1px solid rgba(26, 23, 19, .14);
        }

        .grimba-ads-page__cta--ghost:hover,
        .grimba-ads-page__cta--ghost:focus-visible {
            background: rgba(255, 255, 255, .85);
            color: var(--gn-ink, #1a1713);
            border-color: rgba(26, 23, 19, .22);
        }

        .grimba-ads-page__hero-stats {
            display: grid;
            gap: 14px;
        }

        .grimba-ads-page__stat {
            padding: 16px 18px;
            border-radius: 16px;
            background: rgba(255, 255, 255, .76);
            border: 1px solid rgba(26, 23, 19, .08);
        }

        .grimba-ads-page__stat-num {
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 800;
            font-size: 30px;
            line-height: 1;
            letter-spacing: -0.02em;
            color: var(--gn-ink, #1a1713);
        }

        .grimba-ads-page__stat-label {
            margin-top: 2px;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .58));
        }

        .grimba-ads-page__features {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 28px;
        }

        .grimba-ads-page__feature {
            display: grid;
            grid-template-columns: 36px minmax(0, 1fr);
            gap: 14px;
            padding: 18px 20px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .68);
            border: 1px solid rgba(26, 23, 19, .08);
        }

        .grimba-ads-page__feature-icon {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #c0392b 0%, #1a1713 100%);
            color: #fffaf1;
            font-size: 16px;
        }

        .grimba-ads-page__feature h3 {
            margin: 0 0 6px;
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 800;
            font-size: 18px;
            letter-spacing: -0.01em;
        }

        .grimba-ads-page__feature p {
            margin: 0;
            font-size: 14.5px;
            line-height: 1.5;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .65));
        }

        .grimba-ads-page__inventory {
            position: relative;
            overflow: hidden;
            margin-top: 28px;
            padding: 24px 26px;
            border-radius: 16px;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.72), rgba(246, 241, 232, 0.56)),
                rgba(255, 255, 255, 0.62);
            border: 1px solid rgba(26, 23, 19, .08);
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, 0.18),
                0 20px 52px rgba(26, 23, 19, 0.075);
        }
        .grimba-ads-page__inventory::before {
            content: "";
            position: absolute;
            top: 0;
            left: 1rem;
            right: 1rem;
            height: 3px;
            pointer-events: none;
            background: linear-gradient(90deg, transparent, rgba(192, 57, 43, 0.52), rgba(59, 130, 246, 0.42), transparent);
        }
        .grimba-ads-page__inventory > * {
            position: relative;
            z-index: 1;
        }

        .grimba-ads-page__inventory h2 {
            margin: 0 0 4px;
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 800;
            font-size: 22px;
            letter-spacing: -0.01em;
        }

        .grimba-ads-page__inventory-lede {
            margin: 0 0 14px;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .65));
            font-size: 14px;
        }

        .grimba-ads-page__slots {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .grimba-ads-page__slot {
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(26, 23, 19, .06);
            border: 1px solid rgba(26, 23, 19, .10);
            color: var(--gn-ink, #1a1713);
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 12.5px;
            font-weight: 700;
        }

        .grimba-ads-page__slot--active {
            background: linear-gradient(135deg, #c0392b 0%, #1a1713 100%);
            color: #fffaf1;
            border-color: transparent;
        }

        .grimba-ads-page__packs {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 28px;
        }

        .grimba-ads-page__pack {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 24px 22px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .68);
            border: 1px solid rgba(26, 23, 19, .08);
            position: relative;
        }

        .grimba-ads-page__pack--featured {
            background: linear-gradient(180deg, rgba(26, 23, 19, .96), rgba(40, 35, 28, .92));
            color: #fffaf1;
            border-color: rgba(26, 23, 19, .12);
            box-shadow: 0 24px 64px rgba(26, 23, 19, .26);
        }

        .grimba-ads-page__pack--featured h3,
        .grimba-ads-page__pack--featured .grimba-ads-page__pack-price {
            color: #fffaf1;
        }

        .grimba-ads-page__pack-flag {
            position: absolute;
            top: -10px;
            right: 18px;
            padding: 4px 12px;
            border-radius: 999px;
            background: linear-gradient(135deg, #c0392b 0%, #16a34a 200%);
            color: #fffaf1;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .grimba-ads-page__pack h3 {
            margin: 0;
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 800;
            font-size: 22px;
            letter-spacing: -0.01em;
        }

        .grimba-ads-page__pack-price {
            display: flex;
            align-items: baseline;
            gap: 6px;
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 800;
            font-size: 30px;
            letter-spacing: -0.02em;
            color: var(--gn-ink, #1a1713);
        }

        .grimba-ads-page__pack-period {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .58));
            font-family: 'Public Sans', system-ui, sans-serif;
        }

        .grimba-ads-page__pack--featured .grimba-ads-page__pack-period {
            color: rgba(255, 250, 241, .72);
        }

        .grimba-ads-page__pack ul {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .grimba-ads-page__pack li {
            display: grid;
            grid-template-columns: 16px minmax(0, 1fr);
            gap: 8px;
            font-size: 13.5px;
            line-height: 1.45;
        }

        .grimba-ads-page__pack li::before {
            content: "✓";
            font-weight: 800;
            color: #16a34a;
        }

        .grimba-ads-page__pack--featured li::before {
            color: #b6e7c2;
        }

        .grimba-ads-page__pack-cta {
            margin-top: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 18px;
            border-radius: 999px;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-weight: 800;
            font-size: 13px;
            letter-spacing: .02em;
            text-decoration: none;
            background: #14110d;
            color: #fffaf1;
            transition: transform .2s ease, filter .2s ease;
        }

        .grimba-ads-page__pack-cta:hover,
        .grimba-ads-page__pack-cta:focus-visible {
            transform: translateY(-1px);
            filter: brightness(1.08);
            color: #fffaf1;
            text-decoration: none;
        }

        .grimba-ads-page__pack--featured .grimba-ads-page__pack-cta {
            background: #fffaf1;
            color: #14110d;
        }

        .grimba-ads-page__login {
            margin-top: 32px;
            padding: 24px 26px;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(26, 23, 19, .94), rgba(40, 35, 28, .9));
            color: #fffaf1;
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) auto;
            gap: 18px;
            align-items: center;
        }

        .grimba-ads-page__login h2 {
            margin: 0 0 6px;
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 800;
            font-size: 22px;
            letter-spacing: -0.01em;
            color: #fffaf1;
        }

        .grimba-ads-page__login p {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
            color: rgba(255, 250, 241, .78);
            max-width: 60ch;
        }

        .grimba-ads-page__login-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 22px;
            border-radius: 999px;
            background: #fffaf1;
            color: #14110d;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-weight: 800;
            font-size: 13.5px;
            letter-spacing: .02em;
            text-decoration: none;
            box-shadow: 0 12px 32px rgba(255, 250, 241, .18);
        }

        .grimba-ads-page__login-cta:hover,
        .grimba-ads-page__login-cta:focus-visible {
            color: #14110d;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .grimba-ads-page__login-soon {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-left: 8px;
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(192, 57, 43, .92);
            color: #fffaf1;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        [data-bs-theme="dark"] .grimba-ads-page__hero,
        body[data-theme="dark"] .grimba-ads-page__hero,
        [data-bs-theme="dark"] .grimba-ads-page__feature,
        body[data-theme="dark"] .grimba-ads-page__feature,
        [data-bs-theme="dark"] .grimba-ads-page__inventory,
        body[data-theme="dark"] .grimba-ads-page__inventory,
        [data-bs-theme="dark"] .grimba-ads-page__stat,
        body[data-theme="dark"] .grimba-ads-page__stat,
        [data-bs-theme="dark"] .grimba-ads-page__pack:not(.grimba-ads-page__pack--featured),
        body[data-theme="dark"] .grimba-ads-page__pack:not(.grimba-ads-page__pack--featured) {
            background: rgba(28, 24, 17, .72);
            border-color: rgba(255, 250, 241, .14);
            color: #fffaf1;
        }

        [data-bs-theme="dark"] .grimba-ads-page__hero h1,
        body[data-theme="dark"] .grimba-ads-page__hero h1,
        [data-bs-theme="dark"] .grimba-ads-page__feature h3,
        body[data-theme="dark"] .grimba-ads-page__feature h3,
        [data-bs-theme="dark"] .grimba-ads-page__inventory h2,
        body[data-theme="dark"] .grimba-ads-page__inventory h2,
        [data-bs-theme="dark"] .grimba-ads-page__pack:not(.grimba-ads-page__pack--featured) h3,
        body[data-theme="dark"] .grimba-ads-page__pack:not(.grimba-ads-page__pack--featured) h3,
        [data-bs-theme="dark"] .grimba-ads-page__stat-num,
        body[data-theme="dark"] .grimba-ads-page__stat-num,
        [data-bs-theme="dark"] .grimba-ads-page__pack-price,
        body[data-theme="dark"] .grimba-ads-page__pack-price {
            color: #fffaf1;
        }

        @media (max-width: 991.98px) {
            .grimba-ads-page__hero {
                grid-template-columns: 1fr;
                align-items: start;
            }
            .grimba-ads-page__features,
            .grimba-ads-page__packs {
                grid-template-columns: 1fr;
            }
            .grimba-ads-page__login {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <section class="grimba-ads-page">
        <header class="grimba-ads-page__hero">
            <div>
                <span class="grimba-ads-page__kicker">
                    <span class="grimba-ads-page__kicker-dot" aria-hidden="true"></span>
                    {{ __('GrimbaNews · Annonceurs') }}
                </span>
                <h1>{{ __('Toucher les lecteurs qui comparent chaque camp.') }}</h1>
                <p class="grimba-ads-page__lede">
                    {{ __("Sponsorisez des emplacements premium sur la home, les pages article, et les dossiers multi-sources. Vos messages atterrissent dans un contexte de confiance éditoriale, pas un scroll passif.") }}
                </p>
                <div class="grimba-ads-page__ctas">
                    <a href="{{ url('/advertise/login') }}" class="grimba-ads-page__cta grimba-ads-page__cta--primary">
                        {{ __('Espace annonceurs') }}
                        <span class="grimba-ads-page__login-soon">{{ __('Bientôt') }}</span>
                    </a>
                    <a href="{{ $mailto }}" class="grimba-ads-page__cta grimba-ads-page__cta--ghost">
                        {{ __("Parler à l'équipe ventes") }}
                    </a>
                </div>
            </div>

            <aside class="grimba-ads-page__hero-stats" aria-label="{{ __('Audience') }}">
                <div class="grimba-ads-page__stat">
                    <div class="grimba-ads-page__stat-num">4</div>
                    <div class="grimba-ads-page__stat-label">{{ __('éditions régionales') }}</div>
                </div>
                <div class="grimba-ads-page__stat">
                    <div class="grimba-ads-page__stat-num">FR · EN</div>
                    <div class="grimba-ads-page__stat-label">{{ __('langues actives') }}</div>
                </div>
                <div class="grimba-ads-page__stat">
                    <div class="grimba-ads-page__stat-num">12+</div>
                    <div class="grimba-ads-page__stat-label">{{ __('emplacements sponsor') }}</div>
                </div>
            </aside>
        </header>

        <div class="grimba-ads-page__features">
            @foreach($featureRows as $f)
                <article class="grimba-ads-page__feature">
                    <div class="grimba-ads-page__feature-icon" aria-hidden="true">{{ $f['icon'] }}</div>
                    <div>
                        <h3>{{ $f['title'] }}</h3>
                        <p>{{ $f['body'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        <section class="grimba-ads-page__inventory" aria-labelledby="grimba-ads-page__inventory-title">
            <h2 id="grimba-ads-page__inventory-title">{{ __("Inventaire d'emplacements") }}</h2>
            <p class="grimba-ads-page__inventory-lede">
                @if($slot !== '')
                    {{ __("Vous regardez actuellement : :slot. Sélectionnez d'autres emplacements ou parlez à l'équipe ventes pour une combinaison.", ['slot' => $slot]) }}
                @else
                    {{ __("Sélectionnez les emplacements qui correspondent à votre campagne.") }}
                @endif
            </p>
            <div class="grimba-ads-page__slots" aria-label="{{ __('Emplacements publicitaires') }}">
                @foreach([
                    'home-top' => __('Home top'),
                    'home-mid' => __('Home middle'),
                    'home-native' => __('Home native'),
                    'home-in-feed' => __('Home in-feed'),
                    'article-top' => __('Article top'),
                    'article-mid' => __('Article mid'),
                    'story-after-hero' => __('Dossier hero'),
                    'story-mid' => __('Dossier middle'),
                    'story-sidebar' => __('Dossier sidebar'),
                    'sources-top' => __('Sources top'),
                    'sources-mid' => __('Sources mid'),
                    'page-top' => __('Page top'),
                ] as $key => $label)
                    <span class="grimba-ads-page__slot {{ $key === $slot ? 'grimba-ads-page__slot--active' : '' }}">{{ $label }}</span>
                @endforeach
            </div>
        </section>

        <div class="grimba-ads-page__packs">
            @foreach($packs as $pack)
                <article class="grimba-ads-page__pack {{ ! empty($pack['featured']) ? 'grimba-ads-page__pack--featured' : '' }}">
                    @if(! empty($pack['featured']))
                        <span class="grimba-ads-page__pack-flag" aria-label="{{ __('Le plus choisi') }}">{{ __('Le plus choisi') }}</span>
                    @endif
                    <h3>{{ $pack['tier'] }}</h3>
                    <div class="grimba-ads-page__pack-price">
                        {{ $pack['price'] }}
                        <span class="grimba-ads-page__pack-period">{{ $pack['period'] }}</span>
                    </div>
                    <ul>
                        @foreach($pack['features'] as $feature)
                            <li><span>{{ $feature }}</span></li>
                        @endforeach
                    </ul>
                    <a href="{{ $mailto }}" class="grimba-ads-page__pack-cta">
                        {{ __('Demander un devis') }}
                    </a>
                </article>
            @endforeach
        </div>

        <section class="grimba-ads-page__login" aria-labelledby="grimba-ads-page__login-title">
            <div>
                <h2 id="grimba-ads-page__login-title">{{ __('Espace annonceurs') }}</h2>
                <p>
                    {{ __("Création de campagne en libre-service, télémétrie temps réel, facturation Stripe, file de revue éditoriale. La plateforme annonceurs lance dans les prochains jours — laissez-nous votre adresse via :sales pour avoir un accès anticipé.", ['sales' => '']) }}
                    <a href="{{ $mailto }}" style="color: #fffaf1; text-decoration: underline;">{{ __("Contacter l'équipe ventes") }}</a>
                </p>
            </div>
            <a href="{{ $mailto }}" class="grimba-ads-page__login-cta">
                {{ __("Réserver une démo") }}
            </a>
        </section>
    </section>
@endsection
