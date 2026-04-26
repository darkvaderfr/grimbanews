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
    $configured = $translator->configuredDrivers();
@endphp

@section('content')
    <div class="max-width-900">
        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between">
                <x-core::card.title>GrimbaNews — Traduction</x-core::card.title>
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
                <p class="text-muted small">
                    Clés stockées dans la table <code>settings</code> (préfixe <code>grimba_translator_*_key</code>). Les
                    variables d'environnement restent prises en charge en repli. Laissez un champ vide pour garder la valeur
                    actuelle, ou saisissez <code>__clear__</code> pour l'effacer.
                </p>

                <form method="POST" action="{{ route('grimba.translation.save') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label"><strong>Fournisseur préféré</strong></label>
                        <select name="driver" class="form-select" style="max-width: 300px;">
                            <option value="auto" @selected($pinned === 'auto')>Auto (chaîne de basculement)</option>
                            @foreach($driverLabels as $d => $meta)
                                <option value="{{ $d }}" @selected($pinned === $d)>{{ $meta['name'] }} uniquement</option>
                            @endforeach
                        </select>
                        <div class="form-text">En <em>Auto</em>, la chaîne DeepL → Mistral → OpenRouter → OpenAI → Anthropic → Gemini → xAI → Perplexity → Groq → LibreTranslate est essayée dans l'ordre, avec basculement automatique en cas d'échec.</div>
                    </div>

                    @foreach($driverLabels as $d => $meta)
                        @php $hasValue = ! empty($settings[$d]); @endphp
                        <div class="mb-3">
                            <label class="form-label d-flex align-items-center gap-2">
                                <strong>{{ $meta['name'] }}</strong>
                                @if($hasValue)
                                    <span class="badge bg-success-subtle text-success-emphasis">Configuré</span>
                                @endif
                                <span class="text-muted small">{{ $meta['field'] }}</span>
                            </label>
                            <input type="password" name="{{ $d }}_key" autocomplete="off"
                                   class="form-control"
                                   placeholder="{{ $hasValue ? '••••••••• (laisser vide pour conserver)' : ($d === 'libre' ? 'https://libretranslate.example.com' : 'sk-…') }}">
                            <div class="form-text">{{ $meta['hint'] }}</div>
                        </div>
                    @endforeach

                    <div class="mb-4">
                        <label class="form-label"><strong>Modèles LLM (optionnel)</strong></label>
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

                    <hr class="my-4">

                    <h5>Flux RSS — publication automatique</h5>
                    <div class="form-check form-switch mb-2">
                        <input type="hidden" name="ingest_auto_publish" value="0">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="ingest_auto_publish" name="ingest_auto_publish" value="1"
                               @checked($autoPublish)>
                        <label class="form-check-label" for="ingest_auto_publish">
                            <strong>Publier automatiquement les articles RSS ingérés</strong>
                        </label>
                    </div>
                    <div class="form-text mb-3">
                        Par défaut, chaque article ingéré par le polleur RSS arrive en <code>brouillon</code> et un éditeur
                        le valide depuis <a href="{{ route('grimba.rss-drafts.index') }}">la file d'attente</a>. Activé,
                        les articles sont publiés immédiatement — utile en phase de démo / lancement, à désactiver dès
                        qu'une équipe éditoriale est en place.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>

                <hr class="my-4">

                <h5>Test rapide</h5>
                <p class="text-muted small mb-2">Envoie <code>The quick brown fox jumps over the lazy dog.</code> à travers la chaîne et affiche le résultat.</p>
                <form method="POST" action="{{ route('grimba.translation.test') }}" class="d-flex gap-2 flex-wrap align-items-start">
                    @csrf
                    <select name="driver" class="form-select form-select-sm" style="width: auto;">
                        <option value="auto">Auto (chaîne complète)</option>
                        @foreach($driverLabels as $d => $meta)
                            <option value="{{ $d }}">{{ $meta['name'] }} uniquement</option>
                        @endforeach
                    </select>
                    <input type="text" name="sample" class="form-control form-control-sm" style="max-width: 380px;"
                           placeholder="The quick brown fox jumps over the lazy dog.">
                    <button type="submit" class="btn btn-outline-primary btn-sm">Tester</button>
                </form>

                <hr class="my-4">

                <h5>NobuAI — wrapper LLM public</h5>
                <p class="text-muted small mb-2">
                    Le public voit seulement <strong>NobuAI</strong>. Ce test vérifie le wrapper serveur avec les fournisseurs LLM configurés
                    ({{ $nobuConfigured === [] ? 'aucune clé LLM configurée' : implode(' → ', $nobuConfigured) }}).
                    Le fournisseur préféré ci-dessus pilote aussi NobuAI quand il est compatible LLM.
                </p>
                <form method="POST" action="{{ route('grimba.translation.nobuai-test') }}" class="d-flex gap-2 flex-wrap align-items-start">
                    @csrf
                    <input type="text" name="prompt" class="form-control form-control-sm" style="max-width: 420px;"
                           value="Return exactly OK."
                           placeholder="Return exactly OK.">
                    <button type="submit" class="btn btn-outline-primary btn-sm">Tester NobuAI</button>
                </form>
            </x-core::card.body>
        </x-core::card>
    </div>
@endsection
