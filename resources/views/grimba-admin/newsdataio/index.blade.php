@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1100">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <span>newsdata.io</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Breaking-news pipeline</span>
                <h1 class="grimba-admin-title">newsdata.io provider</h1>
                <p class="grimba-admin-copy">
                    Third breaking-news provider next to GDELT / Google News / Webz / Mediastack. Free plan = 200 credits/day · 10 articles per call. Each request consumes 1 credit. Vader directive 2026-05-16 — stay on free until ad revenue covers a paid sub.
                </p>
            </div>
            <span class="grimba-admin-status">
                {{ $configured ? 'configured' : 'key missing' }}
                · {{ $active ? 'active' : 'paused' }}
                · {{ $stats['used'] }}/{{ $stats['budget'] }} credits today
            </span>
        </section>

        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('test_result'))
            @php $tr = session('test_result'); @endphp
            <div class="alert {{ $tr['ok'] ? 'alert-info' : 'alert-warning' }} mb-3">
                <strong>Test call:</strong>
                {{ $tr['status'] }} · query "{{ $tr['query'] ?? '-' }}" ·
                returned {{ $tr['returned'] ?? 0 }} ·
                ingested {{ $tr['ingested'] ?? 0 }} ·
                deduped {{ $tr['deduped'] ?? 0 }} ·
                {{ $tr['elapsed_ms'] ?? '?' }}ms ·
                remaining {{ $tr['remaining'] ?? '?' }} credits
                @if(! empty($tr['error']))
                    <br><small><strong>Error:</strong> {{ $tr['error'] }}</small>
                @endif
            </div>
        @endif

        @if(session('run_output'))
            <div class="alert alert-secondary mb-3">
                <strong>Run output:</strong>
                <pre class="mb-0" style="white-space:pre-wrap;">{{ session('run_output') }}</pre>
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card p-3 h-100">
                    <small class="text-muted text-uppercase">Credits today</small>
                    <strong class="d-block fs-3">{{ $stats['used'] }} / {{ $stats['budget'] }}</strong>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar @if($stats['pct'] >= 90) bg-danger @elseif($stats['pct'] >= 70) bg-warning @else bg-success @endif"
                             style="width: {{ min(100, $stats['pct']) }}%"></div>
                    </div>
                    @if($stats['remaining'] < 10)
                        <small class="text-danger mt-2 d-block">Approaching daily limit — manual runs may be rejected.</small>
                    @endif
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 h-100">
                    <small class="text-muted text-uppercase">Status</small>
                    <strong class="d-block fs-5">
                        @if(! $configured)
                            Key missing
                        @elseif(! $active)
                            Paused
                        @else
                            Ready
                        @endif
                    </strong>
                    <small class="text-muted">Configured · {{ $configured ? 'yes' : 'no' }}</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 h-100">
                    <small class="text-muted text-uppercase">Cadence</small>
                    <strong class="d-block fs-5">
                        @if($dedicatedCron) every 8 min @else every 15 min (shared) @endif
                    </strong>
                    <small class="text-muted">Max {{ $maxCalls }} call(s)/tick · {{ $pageSize }} articles/call</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 h-100">
                    <small class="text-muted text-uppercase">Last runs (24h)</small>
                    <strong class="d-block fs-3">{{ $recentRuns->count() }}</strong>
                    <small class="text-muted">From provider-runs telemetry</small>
                </div>
            </div>
        </div>

        <x-core::card>
            <x-core::card.header>
                <x-core::card.title>Settings</x-core::card.title>
            </x-core::card.header>
            <x-core::card.body>
                <form method="POST" action="{{ route('grimba.newsdataio.save') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">API key</label>
                        <input type="text" name="key" value="{{ $apiKey }}" class="form-control" placeholder="pub_..." autocomplete="off">
                        <small class="text-muted">Falls back to <code>NEWSDATA_IO_KEY</code> env var when blank.</small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="active" value="1" id="ndi-active" @checked($active)>
                                <label class="form-check-label" for="ndi-active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="dedicated_cron" value="1" id="ndi-cron" @checked($dedicatedCron)>
                                <label class="form-check-label" for="ndi-cron">Dedicated <code>*/8</code> cron</label>
                            </div>
                            <small class="text-muted">Off = share <code>breaking_live</code> cadence (recommended).</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Daily credit budget</label>
                            <input type="number" name="daily_credit_budget" min="1" max="200" value="{{ $budget }}" class="form-control">
                            <small class="text-muted">Free cap = 200. Default 190 leaves operator buffer.</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max calls per run</label>
                            <input type="number" name="max_calls_per_run" min="1" max="6" value="{{ $maxCalls }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Page size (articles/call)</label>
                            <input type="number" name="page_size" min="1" max="10" value="{{ $pageSize }}" class="form-control">
                            <small class="text-muted">Free cap = 10.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Queries (one per line)</label>
                        <textarea name="queries" rows="6" class="form-control" style="font-family:'JetBrains Mono', monospace;">{{ $queries }}</textarea>
                        <small class="text-muted">Newline-separated. Balanced parentheses enforced on save.</small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Languages (max 5)</label>
                            <input type="text" name="languages" value="{{ $languages }}" class="form-control" placeholder="fr,en">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Countries (max 5)</label>
                            <input type="text" name="countries" value="{{ $countries }}" class="form-control" placeholder="fr,sn,ci,ml,cm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categories</label>
                            <input type="text" name="categories" value="{{ $categories }}" class="form-control" placeholder="top,politics,world">
                            <small class="text-muted">newsdata.io taxonomy.</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">HTTP timeout (s)</label>
                            <input type="number" name="timeout" min="2" max="60" value="{{ $timeout }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Connect timeout (s)</label>
                            <input type="number" name="connect_timeout" min="1" max="30" value="{{ $connectTimeout }}" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save settings</button>
                </form>
            </x-core::card.body>
        </x-core::card>

        <div class="d-flex gap-2 my-3">
            <form method="POST" action="{{ route('grimba.newsdataio.test') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-secondary">Test call (costs 1 credit)</button>
            </form>
            <form method="POST" action="{{ route('grimba.newsdataio.run') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-info">Run now</button>
            </form>
        </div>

        <x-core::card>
            <x-core::card.header>
                <x-core::card.title>Recent runs (last 12)</x-core::card.title>
            </x-core::card.header>
            <x-core::card.body>
                @if($recentRuns->isEmpty())
                    <p class="text-muted mb-0">No newsdata.io runs recorded yet. Hit "Test call" to seed one.</p>
                @else
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Started</th>
                                <th>Query</th>
                                <th>Status</th>
                                <th class="text-end">Returned</th>
                                <th class="text-end">Ingested</th>
                                <th class="text-end">Deduped</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentRuns as $r)
                                <tr>
                                    <td><small>{{ $r->started_at }}</small></td>
                                    <td><small>{{ \Illuminate\Support\Str::limit((string) $r->query_label, 40) }}</small></td>
                                    <td>
                                        <span class="badge bg-{{ $r->status === 'ingested' ? 'success' : ($r->status === 'skipped' ? 'secondary' : 'warning') }}">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ $r->returned ?? '-' }}</td>
                                    <td class="text-end">{{ $r->ingested ?? '-' }}</td>
                                    <td class="text-end">{{ $r->deduped ?? '-' }}</td>
                                    <td><small class="text-muted">{{ \Illuminate\Support\Str::limit((string) ($r->error ?? ''), 60) }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-core::card.body>
        </x-core::card>

        <p class="text-muted mt-4 small">
            newsdata.io free plan: 200 credits/day. Each request consumes 1 credit and returns up to 10 articles. We stay on free until revenue covers a subscription (Vader directive 2026-05-16). Reader-facing pages never name newsdata.io — articles surface via the publisher's name (Reuters, AFP, RFI, …) through the standard <code>posts.source_id</code> pipeline.
        </p>
    </div>
@endsection
