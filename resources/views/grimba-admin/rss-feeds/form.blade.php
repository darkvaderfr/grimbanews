@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $isEdit = isset($feed) && $feed;
        $action = $isEdit
            ? route('grimba.rss-feeds.update', $feed->id)
            : route('grimba.rss-feeds.store');
    @endphp

    <div class="grimba-admin-screen max-width-1000">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <a href="{{ route('grimba.rss-feeds.index') }}">Flux RSS</a>
            <span>{{ $isEdit ? 'Modifier' : 'Créer' }}</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">RSS intake</span>
                <h1 class="grimba-admin-title">{{ $isEdit ? 'Edit feed' : 'New RSS feed' }}</h1>
                <p class="grimba-admin-copy">
                    Connect a publisher feed to a classified source. Ingested drafts inherit source bias, ownership, and credibility metadata.
                </p>
            </div>
            <span class="grimba-admin-status">{{ $isEdit ? 'Feed #' . $feed->id : 'Create mode' }}</span>
        </section>

        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between">
                <x-core::card.title>
                    {{ $isEdit ? 'Éditer le flux' : 'Nouveau flux RSS' }}
                </x-core::card.title>
                <a href="{{ route('grimba.rss-feeds.index') }}" class="btn btn-sm btn-outline-secondary">← Retour</a>
            </x-core::card.header>

            @if(session('success_msg'))
                <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger mx-3 mt-3 mb-0">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-core::card.body>
                <form method="POST" action="{{ $action }}" class="grimba-admin-form">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <section class="grimba-admin-form-section">
                        <h2 class="grimba-admin-form-section__title">Connexion du flux</h2>
                        <p class="grimba-admin-form-section__hint mb-3">
                            Reliez chaque URL RSS à une source classée pour que les brouillons héritent des bons signaux éditoriaux.
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Source</label>
                            <select name="source_id" class="form-select" required>
                                <option value="">— Choisir une source —</option>
                                @foreach($sources as $src)
                                    <option value="{{ $src->id }}"
                                        @selected(old('source_id', $feed->source_id ?? '') == $src->id)>
                                        {{ $src->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Les articles ingérés hériteront du biais, propriété et crédibilité de cette source.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">URL du flux</label>
                            <input type="url" name="url" class="form-control"
                                   value="{{ old('url', $feed->url ?? '') }}"
                                   placeholder="https://exemple.com/rss.xml"
                                   required maxlength="500">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Format</label>
                                <select name="feed_format" class="form-select" required>
                                    <option value="rss"  @selected(old('feed_format', $feed->feed_format ?? 'rss') === 'rss')>RSS 2.0</option>
                                    <option value="atom" @selected(old('feed_format', $feed->feed_format ?? 'rss') === 'atom')>Atom</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="form-check form-switch w-100">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="is_active" name="is_active" value="1"
                                           @checked(old('is_active', $feed->is_active ?? true))>
                                    <label class="form-check-label" for="is_active">Flux actif (inclus au poll)</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Remarques internes — quelle rubrique, pourquoi, etc.">{{ old('notes', $feed->notes ?? '') }}</textarea>
                        </div>
                    </section>

                    @if($isEdit)
                        <section class="grimba-admin-form-section">
                            <h2 class="grimba-admin-form-section__title">Diagnostic du poll</h2>
                            <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase">Dernier poll</div>
                                <div>{{ $feed->last_polled_at ? \Carbon\Carbon::parse($feed->last_polled_at)->diffForHumans() : '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase">Échecs consécutifs</div>
                                <div>{{ $feed->consecutive_failures }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase">Articles ingérés</div>
                                <div>{{ $feed->items_ingested }}</div>
                            </div>
                            </div>
                            @if($feed->last_error)
                                <div class="alert alert-warning small mt-3 mb-0">
                                    <strong>Dernière erreur:</strong> {{ $feed->last_error }}
                                </div>
                            @endif
                        </section>
                    @endif

                    <div class="grimba-admin-form-actions">
                        <button type="submit" class="btn btn-primary">
                            {{ $isEdit ? 'Mettre à jour' : 'Créer' }}
                        </button>
                        @if($isEdit)
                            <button type="submit" class="btn btn-outline-primary" form="rss-feed-poll-now">
                                Poll maintenant
                            </button>
                        @endif
                    </div>
                </form>
                @if($isEdit)
                    <form method="POST" action="{{ route('grimba.rss-feeds.poll-now', $feed->id) }}" id="rss-feed-poll-now" class="d-none">
                        @csrf
                    </form>
                @endif
            </x-core::card.body>
        </x-core::card>
    </div>
@endsection
