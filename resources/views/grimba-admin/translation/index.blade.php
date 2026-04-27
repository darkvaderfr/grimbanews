@extends(BaseHelper::getAdminMasterLayoutTemplate())

@php
    $driverLabels = [
        'deepl'      => ['name' => 'DeepL',           'field' => 'DEEPL_API_KEY',      'hint' => 'FR/EN best quality. Free-tier keys end in :fx.'],
        'mistral'    => ['name' => 'Mistral',         'field' => 'MISTRAL_API_KEY',    'hint' => 'FR-native LLM. Uses mistral-small-latest.'],
        'openrouter' => ['name' => 'OpenRouter',      'field' => 'OPENROUTER_API_KEY', 'hint' => '100+ models via one key. Model configurable below.'],
        'openai'     => ['name' => 'OpenAI',          'field' => 'OPENAI_API_KEY',     'hint' => 'gpt-4o-mini. Also shared with AI Writer plugin.'],
        'anthropic'  => ['name' => 'Anthropic',       'field' => 'ANTHROPIC_API_KEY',  'hint' => 'claude-3-5-haiku. Good prose.'],
        'google'     => ['name' => 'Google Gemini',   'field' => 'GOOGLE_API_KEY',     'hint' => 'Gemini 2.0 Flash.'],
        'xai'        => ['name' => 'xAI / Grok',      'field' => 'XAI_API_KEY',        'hint' => 'Grok via the OpenAI-compatible xAI API.'],
        'perplexity' => ['name' => 'Perplexity Sonar', 'field' => 'PERPLEXITY_API_KEY', 'hint' => 'Sonar via OpenAI-compatible chat completions.'],
        'groq'       => ['name' => 'Groq',            'field' => 'GROQ_API_KEY',       'hint' => 'Llama 3.3 70B, very fast, generous free tier.'],
        'libre'      => ['name' => 'LibreTranslate',  'field' => 'LIBRETRANSLATE_URL', 'hint' => 'Self-hosted. Paste the full URL here (e.g. https://translate.example.com).'],
    ];
    $modelDefaults = [
        'mistral' => 'mistral-small-latest',
        'openrouter' => 'mistralai/mistral-small-3-24b-instruct',
        'openai' => 'gpt-4o-mini',
        'anthropic' => 'claude-3-5-haiku-latest',
        'google' => 'gemini-2.0-flash',
        'xai' => 'grok-4.20',
        'perplexity' => 'sonar-pro',
        'groq' => 'llama-3.3-70b-versatile',
    ];
    $providerGroups = [
        'LLM insight providers' => [
            'description' => 'Used by NobuAI story insights and as high-quality translation wrappers.',
            'drivers' => ['openai', 'openrouter', 'anthropic', 'xai', 'mistral', 'google', 'perplexity', 'groq'],
        ],
        'Dedicated translation fallbacks' => [
            'description' => 'Used when a dedicated translation API should handle FR/EN conversion before LLM fallback.',
            'drivers' => ['deepl', 'libre'],
        ],
    ];
    $configured = $translator->configuredDrivers();
@endphp

@section('content')
    <style>
        .grimba-llm-admin {
            --llm-bg: #f6f1e8;
            --llm-panel: #fffaf0;
            --llm-panel-strong: #ffffff;
            --llm-ink: #1a1713;
            --llm-muted: #5f574d;
            --llm-soft: #83786a;
            --llm-rule: rgba(26, 23, 19, 0.12);
            --llm-accent: #c0392b;
            color: var(--llm-ink);
        }
        body[data-bs-theme="dark"] .grimba-llm-admin,
        html[data-bs-theme="dark"] .grimba-llm-admin {
            --llm-bg: #121007;
            --llm-panel: #1c1811;
            --llm-panel-strong: #242016;
            --llm-ink: #f6f1e8;
            --llm-muted: #d7ccb4;
            --llm-soft: #ad9f85;
            --llm-rule: rgba(246, 241, 232, 0.16);
            --llm-accent: #ff9b91;
        }
        .grimba-llm-admin .card,
        .grimba-llm-admin .grimba-llm-hero,
        .grimba-llm-admin .grimba-llm-section,
        .grimba-llm-admin .grimba-provider-card {
            background: var(--llm-panel) !important;
            border: 1px solid var(--llm-rule) !important;
            color: var(--llm-ink) !important;
        }
        .grimba-llm-hero {
            border-radius: 24px;
            padding: clamp(20px, 3vw, 34px);
            margin-bottom: 18px;
            box-shadow: 0 24px 60px rgba(26, 23, 19, 0.10);
        }
        .grimba-llm-kicker {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: var(--llm-ink);
            color: var(--llm-bg);
            padding: 6px 12px;
            font-family: var(--gn-font-mono, ui-monospace, monospace);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .grimba-llm-title {
            color: var(--llm-ink) !important;
            font-family: var(--gn-font-display, Georgia, serif) !important;
            font-size: clamp(34px, 4vw, 58px);
            line-height: 0.95;
            letter-spacing: -0.045em;
            margin: 16px 0 12px;
        }
        .grimba-llm-copy,
        .grimba-llm-admin .text-muted,
        .grimba-llm-admin .form-text {
            color: var(--llm-muted) !important;
        }
        .grimba-llm-section {
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 18px;
        }
        .grimba-provider-card {
            border-radius: 16px;
            padding: 16px;
            height: 100%;
            background: var(--llm-panel-strong) !important;
            box-shadow: 0 14px 34px rgba(26, 23, 19, 0.08);
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }
        .grimba-provider-card:hover {
            border-color: color-mix(in srgb, var(--llm-accent) 34%, var(--llm-rule)) !important;
            box-shadow: 0 18px 42px rgba(26, 23, 19, 0.12);
            transform: translateY(-1px);
        }
        .grimba-provider-group-title {
            color: var(--llm-ink) !important;
            font-family: var(--gn-font-display, Georgia, serif) !important;
            letter-spacing: -0.02em;
        }
        .grimba-provider-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        @media (max-width: 991.98px) {
            .grimba-provider-grid {
                grid-template-columns: 1fr;
            }
        }
        .grimba-provider-card .form-control,
        .grimba-provider-card .form-select,
        .grimba-llm-admin .form-control,
        .grimba-llm-admin .form-select {
            background: var(--llm-panel-strong) !important;
            border-color: var(--llm-rule) !important;
            color: var(--llm-ink) !important;
        }
        .grimba-provider-card .form-control {
            min-height: 44px;
            font-family: var(--gn-font-mono, ui-monospace, monospace);
            letter-spacing: 0.01em;
        }
        .grimba-provider-card .form-control::placeholder,
        .grimba-llm-admin .form-control::placeholder {
            color: var(--llm-soft) !important;
        }
        .grimba-llm-admin code {
            color: var(--llm-accent);
            background: color-mix(in srgb, var(--llm-accent) 10%, transparent);
            border-radius: 6px;
            padding: 2px 5px;
        }
        .grimba-provider-meta {
            color: var(--llm-soft);
            font-size: 12px;
            font-family: var(--gn-font-mono, ui-monospace, monospace);
            word-break: break-word;
        }
        .grimba-status-pill {
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .grimba-status-pill.is-on {
            background: rgba(34, 197, 94, 0.18);
            color: #15803d;
        }
        body[data-bs-theme="dark"] .grimba-status-pill.is-on,
        html[data-bs-theme="dark"] .grimba-status-pill.is-on {
            color: #86efac;
        }
        .grimba-status-pill.is-off {
            background: rgba(131, 120, 106, 0.16);
            color: var(--llm-muted);
        }
        .grimba-llm-actionbar {
            position: sticky;
            bottom: 16px;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--llm-rule);
            border-radius: 16px;
            background: color-mix(in srgb, var(--llm-panel-strong) 94%, transparent);
            box-shadow: 0 18px 44px rgba(26, 23, 19, 0.12);
        }
        .grimba-llm-test-form {
            display: grid;
            grid-template-columns: minmax(150px, .8fr) minmax(110px, .45fr) minmax(110px, .45fr) minmax(220px, 1.2fr) auto;
            gap: 8px;
            align-items: start;
        }
        @media (max-width: 1199.98px) {
            .grimba-llm-test-form {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 575.98px) {
            .grimba-llm-actionbar,
            .grimba-llm-test-form {
                display: flex;
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>

    <div class="grimba-llm-admin max-width-1200 grimba-admin-form">
        <section class="grimba-llm-hero">
            <span class="grimba-llm-kicker">NobuAI Provider Vault</span>
            <h1 class="grimba-llm-title">LLM keys and translation controls</h1>
            <p class="grimba-llm-copy mb-0" style="max-width: 760px;">
                Add provider keys here. Readers only see <strong>NobuAI</strong>; this backend can route through OpenAI,
                OpenRouter, Anthropic, xAI, Mistral, Gemini, Perplexity, Groq, DeepL, or LibreTranslate.
            </p>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="grimba-status-pill {{ count($configured) ? 'is-on' : 'is-off' }}">
                    Translation: {{ count($configured) ? count($configured) . ' configured' : 'not configured' }}
                </span>
                <span class="grimba-status-pill {{ count($nobuConfigured) ? 'is-on' : 'is-off' }}">
                    NobuAI: {{ count($nobuConfigured) ? count($nobuConfigured) . ' LLM ready' : 'no LLM key' }}
                </span>
            </div>
        </section>

        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between">
                <x-core::card.title>Provider configuration</x-core::card.title>
                <div class="small text-muted">
                    {{ count($configured) }} / {{ count($drivers) }} fournisseurs configurés
                    @if(count($configured))
                        · chaîne: {{ implode(' → ', $configured) }}
                    @endif
                </div>
            </x-core::card.header>

            @if(session('success_msg'))
                <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
            @endif

            <x-core::card.body>
                <p class="text-muted small mb-4">
                    Clés stockées dans la table <code>settings</code> (préfixe <code>grimba_translator_*_key</code>). Les
                    variables d'environnement restent prises en charge en repli. Laissez un champ vide pour garder la valeur
                    actuelle, ou saisissez <code>__clear__</code> pour l'effacer.
                </p>

                <section class="grimba-llm-section grimba-admin-form-section">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                        <div>
                            <h3 class="h5 mb-1">Provider diagnostics</h3>
                            <p class="form-text mb-0">
                                Dernières erreurs NobuAI enregistrées côté admin seulement. Les pages publiques restent brandées NobuAI.
                            </p>
                        </div>
                        <span class="grimba-status-pill {{ empty($nobuFailures) ? 'is-on' : 'is-off' }}">
                            {{ empty($nobuFailures) ? 'Aucune erreur' : count($nobuFailures) . ' erreur(s)' }}
                        </span>
                    </div>
                </section>

                <form method="POST" action="{{ route('grimba.translation.save') }}" class="grimba-admin-form">
                    @csrf

                    <div class="grimba-llm-section grimba-admin-form-section">
                        <label class="form-label"><strong>Fournisseur préféré</strong></label>
                        <select name="driver" class="form-select" style="max-width: 300px;">
                            <option value="auto" @selected($pinned === 'auto')>Auto (chaîne de basculement)</option>
                            @foreach($driverLabels as $d => $meta)
                                <option value="{{ $d }}" @selected($pinned === $d)>{{ $meta['name'] }} uniquement</option>
                            @endforeach
                        </select>
                        <div class="form-text">En <em>Auto</em>, la chaîne DeepL → Mistral → OpenRouter → OpenAI → Anthropic → Gemini → xAI → Perplexity → Groq → LibreTranslate est essayée dans l'ordre, avec basculement automatique en cas d'échec.</div>
                    </div>

                    @foreach($providerGroups as $groupTitle => $group)
                        <div class="grimba-llm-section grimba-admin-form-section">
                            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                                <div>
                                    <h3 class="h4 grimba-provider-group-title mb-1">{{ $groupTitle }}</h3>
                                    <p class="form-text mb-0">{{ $group['description'] }}</p>
                                </div>
                                <span class="grimba-status-pill is-off">{{ count($group['drivers']) }} providers</span>
                            </div>

                            <div class="grimba-provider-grid">
                                @foreach($group['drivers'] as $d)
                                    @php
                                        $meta = $driverLabels[$d];
                                        $hasValue = ! empty($settings[$d]);
                                        $failure = $nobuFailures[$d] ?? null;
                                    @endphp
                                    <div class="grimba-provider-card">
                                        <label class="form-label d-flex align-items-center justify-content-between gap-2">
                                            <span>
                                                <strong>{{ $meta['name'] }}</strong>
                                                <span class="grimba-provider-meta d-block">{{ $meta['field'] }}</span>
                                            </span>
                                            <span class="grimba-status-pill {{ $hasValue ? 'is-on' : 'is-off' }}">
                                                {{ $hasValue ? 'Configuré' : 'Vide' }}
                                            </span>
                                        </label>
                                        <input type="password" name="{{ $d }}_key" autocomplete="off"
                                               class="form-control"
                                               placeholder="{{ $hasValue ? '••••••••• (laisser vide pour conserver)' : ($d === 'libre' ? 'https://libretranslate.example.com' : 'sk-...') }}">
                                        <div class="form-text">{{ $meta['hint'] }}</div>
                                        @if($failure)
                                            <div class="grimba-provider-meta mt-2">
                                                Dernier échec {{ ! empty($failure['at']) ? \Carbon\Carbon::parse($failure['at'])->locale('fr')->diffForHumans() : 'date inconnue' }} :
                                                {{ $failure['message'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                    <div class="grimba-llm-section grimba-admin-form-section">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                                <h3 class="h5 mb-1">Modèles LLM optionnels</h3>
                                <p class="form-text mb-0">Laissez vide pour utiliser le modèle par défaut du provider.</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            @foreach($modelDefaults as $d => $default)
                                <div class="col-md-6">
                                    <label class="form-label small text-muted mb-1">{{ $driverLabels[$d]['name'] }}</label>
                                    <input type="text"
                                           name="{{ $d }}_model"
                                           value="{{ $models[$d] ?? '' }}"
                                           class="form-control"
                                           placeholder="{{ $default }}">
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">
                            Laissez vide pour utiliser le modèle par défaut. Ces valeurs alimentent la traduction et le wrapper NobuAI.
                            Pour OpenRouter, voir <a href="https://openrouter.ai/models" target="_blank" rel="noopener">openrouter.ai/models</a>.
                        </div>
                    </div>

                    <div class="grimba-llm-section grimba-admin-form-section">
                        <h3 class="h5">Flux RSS — publication automatique</h3>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="ingest_auto_publish" value="0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="ingest_auto_publish" name="ingest_auto_publish" value="1"
                                   @checked($autoPublish)>
                            <label class="form-check-label" for="ingest_auto_publish">
                                <strong>Publier automatiquement les articles RSS ingérés</strong>
                            </label>
                        </div>
                        <div class="form-text mb-0">
                            Par défaut, chaque article ingéré par le polleur RSS arrive en <code>brouillon</code> et un éditeur
                            le valide depuis <a href="{{ route('grimba.rss-drafts.index') }}">la file d'attente</a>. Activé,
                            les articles sont publiés immédiatement.
                        </div>
                    </div>

                    <div class="grimba-llm-actionbar">
                        <div class="small text-muted">
                            Empty fields keep existing secrets. Use <code>__clear__</code> to remove a saved key.
                        </div>
                        <button type="submit" class="btn btn-primary">Enregistrer les clés NobuAI</button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="row g-3">
                    <div class="col-lg-6">
                        <section class="grimba-llm-section grimba-admin-form-section h-100">
                            <h3 class="h5">Test rapide</h3>
                            <p class="text-muted small mb-2">Teste la traduction dans les deux sens. Le site public utilise la même chaîne NobuAI.</p>
                            <form method="POST" action="{{ route('grimba.translation.test') }}" class="grimba-llm-test-form">
                                @csrf
                                <select name="driver" class="form-select form-select-sm">
                                    <option value="auto">Auto (chaîne complète)</option>
                                    @foreach($driverLabels as $d => $meta)
                                        <option value="{{ $d }}">{{ $meta['name'] }} uniquement</option>
                                    @endforeach
                                </select>
                                <select name="from" class="form-select form-select-sm">
                                    <option value="en">EN source</option>
                                    <option value="fr">FR source</option>
                                </select>
                                <select name="to" class="form-select form-select-sm">
                                    <option value="fr">FR cible</option>
                                    <option value="en">EN cible</option>
                                </select>
                                <input type="text" name="sample" class="form-control form-control-sm"
                                       placeholder="The quick brown fox jumps over the lazy dog.">
                                <button type="submit" class="btn btn-outline-primary btn-sm">Tester</button>
                            </form>
                        </section>
                    </div>
                    <div class="col-lg-6">
                        <section class="grimba-llm-section grimba-admin-form-section h-100">
                            <h3 class="h5">NobuAI — wrapper LLM public</h3>
                            <p class="text-muted small mb-2">
                                Le public voit seulement <strong>NobuAI</strong>. Fournisseurs LLM configurés :
                                {{ $nobuConfigured === [] ? 'aucune clé LLM configurée' : implode(' → ', $nobuConfigured) }}.
                            </p>
                            <form method="POST" action="{{ route('grimba.translation.nobuai-test') }}" class="d-flex gap-2 flex-wrap align-items-start">
                                @csrf
                                <input type="text" name="prompt" class="form-control form-control-sm" style="max-width: 420px;"
                                       value="Return exactly OK."
                                       placeholder="Return exactly OK.">
                                <button type="submit" class="btn btn-outline-primary btn-sm">Tester NobuAI</button>
                            </form>
                        </section>
                    </div>
                </div>
            </x-core::card.body>
        </x-core::card>
    </div>
@endsection
