@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1100">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <span>Translation map</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Language work map</span>
                <h1 class="grimba-admin-title">Translation work-map</h1>
                <p class="grimba-admin-copy">
                    What still needs translating per locale. Driven entirely by
                    <code>posts.original_language</code> + <code>grimba_post_translations</code>.
                    Read-only — actual translation happens in the scheduled
                    <code>grimba:translate-pending</code> job.
                </p>
            </div>
            <span class="grimba-admin-status">
                {{ number_format($totalDone) }} done · {{ number_format($totalPending) }} pending · {{ number_format($unclassifiedCount) }} unclassified
            </span>
        </section>

        @if(! $hasOriginalLang)
            <div class="alert alert-warning mb-3">
                <code>posts.original_language</code> column is missing — run migration
                <code>2026_04_24_000000_add_original_language_to_posts.php</code> first.
            </div>
        @endif

        <div class="row g-3 mb-3">
            @foreach($work as $target => $row)
                @php
                    $pct = $row['total'] > 0 ? (int) round($row['done'] * 100 / $row['total']) : 0;
                @endphp
                <div class="col-md-6">
                    <div class="card p-3 h-100">
                        <small class="text-muted text-uppercase">{{ strtoupper($row['source']) }} → {{ strtoupper($target) }}</small>
                        <strong class="d-block fs-3">{{ number_format($row['pending']) }}</strong>
                        <small class="text-muted">pending translations</small>
                        <div class="progress mt-2" style="height: 8px;">
                            <div class="progress-bar @if($pct >= 90) bg-success @elseif($pct >= 50) bg-info @else bg-warning @endif"
                                 style="width: {{ max(2, $pct) }}%"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">{{ number_format($row['done']) }}/{{ number_format($row['total']) }} translated ({{ $pct }}%)</small>
                    </div>
                </div>
            @endforeach
        </div>

        @if($unclassifiedCount > 0)
            <div class="alert alert-info mb-3">
                <strong>{{ number_format($unclassifiedCount) }} posts have no origin-language tag yet.</strong>
                They sit outside the FR↔EN translation flow until classified. Run
                <code>php artisan grimba:backfill-language</code> (or wait for the 03:15 UTC daily cron).
            </div>
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <x-core::card>
                    <x-core::card.header>
                        <x-core::card.title>EN → FR backlog by source (top 15)</x-core::card.title>
                    </x-core::card.header>
                    <x-core::card.body>
                        @if($perSourceFr->isEmpty())
                            <p class="text-muted mb-0">No EN posts awaiting French translation.</p>
                        @else
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Source</th><th class="text-end">Pending FR</th></tr></thead>
                                <tbody>
                                    @foreach($perSourceFr as $r)
                                        <tr>
                                            <td><small>{{ $r->source_name ?: '(unknown)' }}</small></td>
                                            <td class="text-end">{{ number_format($r->pending) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </x-core::card.body>
                </x-core::card>
            </div>
            <div class="col-md-6">
                <x-core::card>
                    <x-core::card.header>
                        <x-core::card.title>FR → EN backlog by source (top 15)</x-core::card.title>
                    </x-core::card.header>
                    <x-core::card.body>
                        @if($perSourceEn->isEmpty())
                            <p class="text-muted mb-0">No FR posts awaiting English translation.</p>
                        @else
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Source</th><th class="text-end">Pending EN</th></tr></thead>
                                <tbody>
                                    @foreach($perSourceEn as $r)
                                        <tr>
                                            <td><small>{{ $r->source_name ?: '(unknown)' }}</small></td>
                                            <td class="text-end">{{ number_format($r->pending) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </x-core::card.body>
                </x-core::card>
            </div>
        </div>

        {{-- S-LANG-13 (Vader 2026-05-17) — per-source coverage table.
             Spot publishers whose article archive is heavy on one
             locale or has a big unclassified pool. --}}
        @if($perSourceCoverage->isNotEmpty())
            <x-core::card class="mt-4">
                <x-core::card.header>
                    <x-core::card.title>Per-source coverage (top 40 by total)</x-core::card.title>
                </x-core::card.header>
                <x-core::card.body>
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">FR</th>
                                <th class="text-end">EN</th>
                                <th class="text-end">Unknown</th>
                                <th class="text-end">In-row translated</th>
                                <th>Source lang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($perSourceCoverage as $row)
                                @php
                                    $total       = (int) $row->total;
                                    $unknownPct  = $total > 0 ? round((int) $row->unknown_count * 100 / $total, 1) : 0;
                                    $unknownTone = $unknownPct >= 30 ? 'danger' : ($unknownPct >= 10 ? 'warning' : 'success');
                                @endphp
                                <tr>
                                    <td><small>{{ $row->name }}</small></td>
                                    <td class="text-end"><strong>{{ number_format($total) }}</strong></td>
                                    <td class="text-end">{{ number_format((int) $row->fr_count) }}</td>
                                    <td class="text-end">{{ number_format((int) $row->en_count) }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $unknownTone }}">{{ number_format((int) $row->unknown_count) }} ({{ $unknownPct }}%)</span>
                                    </td>
                                    <td class="text-end">{{ number_format((int) $row->in_row_translated) }}</td>
                                    <td>
                                        @if($row->source_lang)
                                            <code>{{ $row->source_lang }}</code>
                                        @else
                                            <small class="text-muted">unset</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-core::card.body>
            </x-core::card>
        @endif

        <p class="text-muted mt-4 small">
            How this is computed: pending = posts where <code>original_language</code> is the opposite locale AND no translated row exists (neither in <code>posts.translated_*</code> nor in <code>grimba_post_translations</code>). Posts with <code>original_language=NULL</code> are counted separately as <em>unclassified</em> and don't enter the translate-pending queue until the detector tags them.
        </p>
    </div>
@endsection
