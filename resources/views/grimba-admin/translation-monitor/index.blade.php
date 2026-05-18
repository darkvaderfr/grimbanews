@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <span>Surface</span>
            <a href="{{ route('grimba.translation-rules.index') }}">Règles de traduction</a>
            <span>Moniteur</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Commande surface</span>
                <h1 class="grimba-admin-title">Moniteur de traduction</h1>
                <p class="grimba-admin-copy">
                    Activité du moteur de règles (cron */15) — décisions récentes, file d'attente,
                    appels du jour. Les décisions sont conservées 36 h en cache.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                @if($enabled)
                    <span class="grimba-admin-status">{{ __('Moteur ACTIF') }}</span>
                @else
                    <span class="grimba-admin-status" style="background:#c0392b;color:#fffaf1;">{{ __('Moteur DÉSACTIVÉ') }}</span>
                @endif
                <a href="{{ route('grimba.translation-rules.index') }}" class="btn btn-outline-primary btn-sm">{{ __('Régler les conditions') }}</a>
            </div>
        </section>

        @if(session('success_msg'))
            <div class="alert alert-success">{{ session('success_msg') }}</div>
        @endif

        {{-- S-LSAT-19b — provider visibility. If zero providers
             are configured the engine silently no-ops; this card
             surfaces that condition prominently. --}}
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="grimba-admin-stat rounded-3 p-3 d-flex align-items-center justify-content-between flex-wrap gap-2"
                     style="background: rgba({{ ($providerStatus['enabled'] ?? false) ? '22, 101, 52' : '192, 57, 43' }}, 0.08); border: 1px solid rgba({{ ($providerStatus['enabled'] ?? false) ? '22, 101, 52' : '192, 57, 43' }}, 0.22);">
                    <div>
                        <div style="font-family: 'Public Sans', system-ui, sans-serif; font-weight: 800; font-size: 11px; letter-spacing: 0.16em; text-transform: uppercase; opacity: 0.62;">
                            {{ __('Drivers traducteur') }}
                        </div>
                        <div style="font-size: 14px; margin-top: 4px;">
                            @if(empty($providerStatus['configured']))
                                <span style="color:#c0392b; font-weight:700;">{{ __('Aucun driver configuré — le moteur de règles est en no-op silencieux.') }}</span>
                            @else
                                {{ __(':n driver(s) prêt(s)', ['n' => count($providerStatus['configured'])]) }} :
                                @foreach($providerStatus['configured'] as $drv)
                                    <span style="display:inline-block; padding:2px 9px; margin:2px 4px 2px 0; border-radius:999px; background:rgba(26,23,19,.08); color:#1a1713; font-family:'JetBrains Mono', ui-monospace, monospace; font-size:11px; font-weight:700;">{{ $drv }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @if(empty($providerStatus['configured']))
                        <a href="{{ url('/admin/grimba/translation') }}" class="btn btn-outline-primary btn-sm">{{ __('Régler les clés API') }}</a>
                    @endif
                </div>
            </div>
        </div>

        @php
            $capPct = $cap > 0 ? min(100, (int) round($callsToday / $cap * 100)) : 0;
            $capExhausted = $enabled && $cap > 0 && $callsToday >= $cap;
            $capWarning = $enabled && ! $capExhausted && $capPct >= 90;
        @endphp

        @if($capExhausted)
            <div class="alert" role="alert" style="background: rgba(192, 57, 43, 0.10); border: 1px solid rgba(192, 57, 43, 0.32); color: #c0392b;">
                <strong>{{ __('Plafond quotidien atteint :') }}</strong>
                {{ __(":n / :cap appels effectués aujourd'hui. Le moteur ne traduira plus rien avant minuit (locale du serveur). Pour augmenter le plafond, voir l'écran Règles de traduction.", ['n' => $callsToday, 'cap' => $cap]) }}
                <a href="{{ route('grimba.translation-rules.index') }}" class="btn btn-outline-danger btn-sm ms-2">{{ __('Régler le plafond') }}</a>
            </div>
        @elseif($capWarning)
            <div class="alert" role="alert" style="background: rgba(217, 119, 6, 0.10); border: 1px solid rgba(217, 119, 6, 0.32); color: #b45309;">
                <strong>{{ __('Plafond proche :') }}</strong>
                {{ __(":pct % du plafond consommé (:n / :cap). Le moteur s'arrêtera quand le compteur atteindra :cap.", ['pct' => $capPct, 'n' => $callsToday, 'cap' => $cap]) }}
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-3 col-6">
                <div class="grimba-admin-stat rounded-3 p-3 h-100 text-center">
                    <div class="grimba-admin-metric-value">{{ $callsToday }}<span style="opacity:.5; font-size:.5em; font-weight:400;"> / {{ $cap }}</span></div>
                    <div class="grimba-admin-metric-label">Appels aujourd'hui</div>
                    <div style="height:4px; background:rgba(26,23,19,.10); border-radius:2px; margin-top:8px; overflow:hidden;">
                        <div style="height:100%; width:{{ $capPct }}%; background:linear-gradient(90deg, #166534, #c0392b); transition:width .3s ease;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="grimba-admin-stat rounded-3 p-3 h-100 text-center">
                    <div class="grimba-admin-metric-value" style="color:#c0392b;">{{ $pinnedQueue }}</div>
                    <div class="grimba-admin-metric-label">Épinglés (priorité 2)</div>
                    <div style="font-size:11px; opacity:.6; margin-top:4px;">en attente de traduction</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="grimba-admin-stat rounded-3 p-3 h-100 text-center">
                    <div class="grimba-admin-metric-value" style="color:var(--gn-ink-soft);">{{ $ruleQueue }}</div>
                    <div class="grimba-admin-metric-label">Règle (priorité 1)</div>
                    <div style="font-size:11px; opacity:.6; margin-top:4px;">candidats du moteur</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="grimba-admin-stat rounded-3 p-3 h-100 text-center">
                    <div class="grimba-admin-metric-value" style="color:#166534;">{{ count($decisions) }}</div>
                    <div class="grimba-admin-metric-label">Décisions loggées</div>
                    <div style="font-size:11px; opacity:.6; margin-top:4px;">36 h glissantes</div>
                </div>
            </div>
        </div>

        <x-core::card class="mb-3">
            <x-core::card.header class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <x-core::card.title>Décisions récentes du moteur</x-core::card.title>
                <div class="grimba-admin-actions">
                    @if(! empty($decisions))
                        <form method="POST" action="{{ route('grimba.translation-monitor.clear') }}" class="d-inline" onsubmit="return confirm('Vider le journal des décisions ?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Vider le journal</button>
                        </form>
                    @endif
                </div>
            </x-core::card.header>
            <x-core::card.body>
                @if(empty($decisions))
                    <p style="opacity:.6; margin:0;">
                        {{ __('Aucune décision loggée dans les 36 dernières heures. Le cron */15 enregistrera la prochaine activité.') }}
                    </p>
                @else
                    <div class="table-responsive grimba-admin-table-responsive">
                        <table class="table table-striped align-middle grimba-admin-table" style="font-size:13px;">
                            <thead>
                                <tr>
                                    <th>{{ __('Quand') }}</th>
                                    <th>{{ __('Post') }}</th>
                                    <th>{{ __('De → vers') }}</th>
                                    <th>{{ __('Région') }}</th>
                                    <th>{{ __('Vues') }}</th>
                                    <th>{{ __('Raison') }}</th>
                                    <th>{{ __('Issue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($decisions as $d)
                                    @php
                                        $ts = $d['ts'] ?? null;
                                        $time = $ts ? \Illuminate\Support\Carbon::parse($ts)->diffForHumans() : '—';
                                        $outcomeColor = match ($d['outcome'] ?? '') {
                                            'ok' => '#166534',
                                            'fail' => '#c0392b',
                                            'dry' => '#6b6459',
                                            default => '#1a1713',
                                        };
                                    @endphp
                                    <tr>
                                        <td style="white-space:nowrap; font-size:12px;">{{ $time }}</td>
                                        <td>
                                            <a href="{{ url('/admin/posts/edit/' . ($d['post_id'] ?? 0)) }}" style="color: var(--gn-left); text-decoration: underline; font-weight:600;">#{{ $d['post_id'] ?? '—' }}</a>
                                            <div style="font-size:11.5px; opacity:.7; max-width:480px;">{{ \Illuminate\Support\Str::limit((string) ($d['title'] ?? ''), 100) }}</div>
                                        </td>
                                        <td style="font-family:'JetBrains Mono', ui-monospace, monospace; font-size:12px;">
                                            {{ ($d['from'] ?? '?') }} → {{ ($d['to'] ?? '?') }}
                                        </td>
                                        <td>{{ $d['region'] ?? '—' }}</td>
                                        <td>{{ number_format((int) ($d['views'] ?? 0)) }}</td>
                                        <td style="font-family:'JetBrains Mono', ui-monospace, monospace; font-size:11px; max-width:280px;">{{ $d['reason'] ?? '—' }}</td>
                                        <td>
                                            <span style="display:inline-block; padding:2px 8px; border-radius:999px; background:rgba(26,23,19,.08); color:{{ $outcomeColor }}; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.08em;">
                                                {{ $d['outcome'] ?? '—' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-core::card.body>
        </x-core::card>

        <x-core::card>
            <x-core::card.header>
                <x-core::card.title>Traductions récentes (24 h)</x-core::card.title>
                <small class="text-muted">
                    Vue large incluant manuel + cron + traduction par règle.
                </small>
            </x-core::card.header>
            <x-core::card.body>
                @if($recentlyTranslated->isEmpty())
                    <p style="opacity:.6; margin:0;">{{ __('Aucune traduction enregistrée dans les 24 dernières heures.') }}</p>
                @else
                    <div class="table-responsive grimba-admin-table-responsive">
                        <table class="table table-striped align-middle grimba-admin-table" style="font-size:13px;">
                            <thead>
                                <tr>
                                    <th>{{ __('Quand') }}</th>
                                    <th>{{ __('Post') }}</th>
                                    <th>{{ __('De → vers') }}</th>
                                    <th>{{ __('Driver') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentlyTranslated as $rt)
                                    <tr>
                                        <td style="white-space:nowrap; font-size:12px;">{{ \Illuminate\Support\Carbon::parse($rt->translated_at)->diffForHumans() }}</td>
                                        <td>
                                            <a href="{{ url('/admin/posts/edit/' . $rt->id) }}" style="color: var(--gn-left); text-decoration: underline; font-weight:600;">#{{ $rt->id }}</a>
                                            <div style="font-size:11.5px; opacity:.7; max-width:520px;">
                                                {{ \Illuminate\Support\Str::limit((string) ($rt->translated_name ?: $rt->name), 100) }}
                                            </div>
                                        </td>
                                        <td style="font-family:'JetBrains Mono', ui-monospace, monospace; font-size:12px;">
                                            {{ $rt->original_language ?? '?' }} → {{ $rt->translated_to ?? '?' }}
                                        </td>
                                        <td style="font-family:'JetBrains Mono', ui-monospace, monospace; font-size:11px;">
                                            {{ $rt->translation_driver ?: '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-core::card.body>
        </x-core::card>
    </div>
@stop
