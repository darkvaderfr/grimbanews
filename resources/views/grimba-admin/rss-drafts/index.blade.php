@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <x-core::card.title>
                    File RSS — brouillons à réviser
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
                                        @endphp
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="ids[]" value="{{ $d->id }}" class="form-check-input gn-draft-check">
                                            </td>
                                            <td>
                                                <a href="{{ route('posts.edit', $d->id) }}" target="_blank">
                                                    <strong>{{ \Illuminate\Support\Str::limit($d->name, 95) }}</strong>
                                                </a>
                                                @if($d->description)
                                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($d->description), 140) }}</div>
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
                                                        class="btn btn-sm btn-outline-success">Publier</button>
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
                boxes.forEach(function (b) { if (b.checked) n++; });
                if (count) count.textContent = n;
                if (all) {
                    all.checked = n > 0 && n === boxes.length;
                    all.indeterminate = n > 0 && n < boxes.length;
                }
            }

            if (all) {
                all.addEventListener('change', function () {
                    boxes.forEach(function (b) { b.checked = all.checked; });
                    refresh();
                });
            }
            boxes.forEach(function (b) { b.addEventListener('change', refresh); });
            refresh();
        })();
    </script>
@endsection
