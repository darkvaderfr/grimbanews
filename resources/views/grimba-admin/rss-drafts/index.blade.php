@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">RSS command lane</span>
                <h1 class="grimba-admin-title">File RSS à réviser</h1>
                <p class="grimba-admin-copy">
                    Corrigez les garde-fous, publiez uniquement les brouillons prêts, et gardez le flux reader sous contrôle.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <span class="grimba-admin-status">{{ $stats['total_queue'] }} en attente</span>
                <span class="grimba-admin-status">{{ $guardrailStats['blocked'] }} bloqués</span>
            </div>
        </section>

        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <x-core::card.title>
                    Brouillons RSS
                    <span class="text-muted small ms-2">
                        {{ $stats['total_queue'] }} en attente · {{ $stats['total_published'] }} déjà publiés
                    </span>
                </x-core::card.title>

                <form method="GET" action="{{ route('grimba.rss-drafts.index') }}" class="d-flex gap-2 align-items-center">
                    <select name="source" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        <option value="">Toutes sources</option>
                        @foreach($sources as $src)
                            <option value="{{ $src->id }}" @selected((int) $sourceId === (int) $src->id)>{{ $src->name }}</option>
                        @endforeach
                    </select>
                    <select name="bias" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        <option value="">Tous biais</option>
                        <option value="left"    @selected($bias === 'left')>Gauche</option>
                        <option value="center"  @selected($bias === 'center')>Centre</option>
                        <option value="right"   @selected($bias === 'right')>Droite</option>
                        <option value="unknown" @selected($bias === 'unknown')>Non classé</option>
                    </select>
                    @if($sourceId || $bias)
                        <a href="{{ route('grimba.rss-drafts.index') }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
                    @endif
                </form>
            </x-core::card.header>

            @if(session('success_msg'))
                <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
            @endif

            <x-core::card.body>
                <div class="alert alert-secondary">
                    <strong>Blockers RSS:</strong>
                    {{ $guardrailStats['blocked'] }} bloqué(s), {{ $guardrailStats['ready'] }} prêt(s).
                    @foreach($guardrailStats['reasons'] as $reason => $count)
                        @if($count > 0)
                            @php
                                $fixUrl = match ($reason) {
                                    'source manquante', 'biais inconnu' => route('grimba.news-sources.triage'),
                                    'traduction manquante' => route('grimba.translation.index'),
                                    default => route('grimba.rss-drafts.index'),
                                };
                            @endphp
                            <a href="{{ $fixUrl }}" class="badge bg-warning text-dark ms-1 text-decoration-none">{{ $reason }} {{ $count }}</a>
                        @endif
                    @endforeach
                </div>

                @if($drafts->isEmpty())
                    <div class="text-center text-muted py-5">
                        <div class="display-6">📭</div>
                        <p class="mt-2 mb-0">Aucun brouillon RSS à réviser pour ces filtres.</p>
                    </div>
                @else
                    <form method="POST" id="rss-drafts-form">
                        @csrf
                        <div class="d-flex gap-2 mb-3 align-items-center flex-wrap">
                            <button type="submit" formaction="{{ route('grimba.rss-drafts.publish') }}"
                                    class="btn btn-sm btn-success"
                                    onclick="return confirm('Publier les brouillons sélectionnés ?');">
                                ✓ Publier sélection
                            </button>
                            <button type="submit" formaction="{{ route('grimba.rss-drafts.delete') }}"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Supprimer les brouillons sélectionnés ? (Irréversible)');">
                                × Supprimer sélection
                            </button>
                            <span class="text-muted small ms-auto">
                                <span id="gn-draft-count">0</span> sélectionné(s) sur {{ $drafts->count() }} affiché(s)
                            </span>
                            <span class="text-muted small w-100">
                                Les brouillons signalés par les garde-fous sont désactivés jusqu'à correction.
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 30px;">
                                            <input type="checkbox" class="form-check-input" id="gn-select-all">
                                        </th>
                                        <th>Titre</th>
                                        <th>Source</th>
                                        <th>Biais</th>
                                        <th>Reçu</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($drafts as $d)
                                        @php
                                            $biasLabel = ['left'=>'Gauche','center'=>'Centre','right'=>'Droite','unknown'=>'—'][$d->bias_rating] ?? '—';
                                            $biasColor = ['left'=>'#3b82f6','center'=>'#a8a8a8','right'=>'#ef4444','unknown'=>'#9ca3af'][$d->bias_rating] ?? '#9ca3af';
                                            $guardrails = grimba_rss_draft_guardrails($d);
                                            $isReady = empty($guardrails);
                                        @endphp
                                        <tr @class(['table-warning' => ! $isReady])>
                                            <td>
                                                <input type="checkbox" name="ids[]" value="{{ $d->id }}" class="form-check-input gn-draft-check" @disabled(! $isReady)>
                                            </td>
                                            <td>
                                                <a href="{{ route('posts.edit', $d->id) }}" target="_blank">
                                                    <strong>{{ \Illuminate\Support\Str::limit($d->name, 95) }}</strong>
                                                </a>
                                                @if($d->description)
                                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($d->description), 140) }}</div>
                                                @endif
                                                @if(! $isReady)
                                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                                        @foreach($guardrails as $flag)
                                                            @php
                                                                $fixUrl = match ($flag) {
                                                                    'source manquante', 'biais inconnu' => route('grimba.news-sources.triage'),
                                                                    'traduction manquante' => route('grimba.translation.index'),
                                                                    default => route('posts.edit', $d->id),
                                                                };
                                                            @endphp
                                                            <a href="{{ $fixUrl }}" class="badge bg-warning text-dark text-decoration-none">{{ $flag }}</a>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="small text-success mt-1">Prêt à publier</div>
                                                @endif
                                            </td>
                                            <td class="small">{{ $d->source_name ?? '—' }}</td>
                                            <td>
                                                <span class="badge" style="background: {{ $biasColor }}22; color: {{ $biasColor }}; border: 1px solid {{ $biasColor }}44;">
                                                    {{ $biasLabel }}
                                                </span>
                                            </td>
                                            <td class="small text-muted">
                                                {{ \Carbon\Carbon::parse($d->created_at)->diffForHumans() }}
                                            </td>
                                            <td class="text-end">
                                                <button type="submit"
                                                        formaction="{{ route('grimba.rss-drafts.publish-one', $d->id) }}"
                                                        class="btn btn-sm btn-outline-success"
                                                        @disabled(! $isReady)>Publier</button>
                                                <a href="{{ route('posts.edit', $d->id) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-primary">Éditer</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                @endif
            </x-core::card.body>

            <x-core::card.footer>
                {!! $drafts->links() !!}
            </x-core::card.footer>
        </x-core::card>
    </div>

    <script>
        (function () {
            var form  = document.getElementById('rss-drafts-form');
            if (!form) return;

            var all   = document.getElementById('gn-select-all');
            var boxes = form.querySelectorAll('.gn-draft-check');
            var count = document.getElementById('gn-draft-count');

            function refresh() {
                var n = 0;
                var enabled = 0;
                boxes.forEach(function (b) {
                    if (b.disabled) return;
                    enabled++;
                    if (b.checked) n++;
                });
                if (count) count.textContent = n;
                if (all) {
                    all.checked = n > 0 && n === enabled;
                    all.indeterminate = n > 0 && n < enabled;
                }
            }

            if (all) {
                all.addEventListener('change', function () {
                    boxes.forEach(function (b) {
                        if (!b.disabled) b.checked = all.checked;
                    });
                    refresh();
                });
            }
            boxes.forEach(function (b) { b.addEventListener('change', refresh); });
            refresh();
        })();
    </script>
@endsection
