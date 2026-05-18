@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <span>Surface</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Commande surface</span>
                <h1 class="grimba-admin-title">Rails de la home</h1>
                <p class="grimba-admin-copy">
                    Épinglez les sections de la page d'accueil à une catégorie précise.
                    Vide = sélection automatique (la catégorie la plus active).
                    Les changements vident le cache home et prennent effet immédiatement.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-secondary btn-sm">{{ __('Voir la home') }}</a>
            </div>
        </section>

        @if(session('success_msg'))
            <div class="alert alert-success">{{ session('success_msg') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('grimba.home-rails.save') }}">
            @csrf

            <x-core::card class="mb-3">
                <x-core::card.header>
                    <x-core::card.title>Sections épinglées</x-core::card.title>
                    <small class="text-muted">
                        Deux slots disponibles. Le slot 1 s'affiche en premier sur la home, le slot 2 ensuite.
                    </small>
                </x-core::card.header>
                <x-core::card.body>
                    @foreach([1, 2] as $slot)
                        <div class="mb-3">
                            <label class="form-label" for="grimba_section_pin_{{ $slot }}_field">
                                {{ __('Slot :n', ['n' => $slot]) }}
                            </label>
                            <select name="grimba_section_pin_{{ $slot }}"
                                    id="grimba_section_pin_{{ $slot }}_field"
                                    class="form-select form-select-sm"
                                    style="max-width: 360px;">
                                <option value="" @selected(empty($current[$slot]))>{{ __('— Sélection automatique —') }}</option>
                                @foreach($topicChoices as $name)
                                    <option value="{{ $name }}" @selected($current[$slot] === $name)>{{ __($name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </x-core::card.body>
            </x-core::card>

            <x-core::card class="mb-3">
                <x-core::card.header>
                    <x-core::card.title>Aperçu actuel</x-core::card.title>
                    <small class="text-muted">
                        Catégories qui seront poussées dans les sections de la home, dans l'ordre d'affichage.
                    </small>
                </x-core::card.header>
                <x-core::card.body>
                    @if($resolved->isEmpty())
                        <em style="opacity:.6;">{{ __('Aucune section configurable — vérifiez que des catégories topic sont publiées.') }}</em>
                    @else
                        <ol class="mb-0" style="padding-left: 18px;">
                            @foreach($resolved as $cat)
                                <li style="margin-bottom: 4px;">
                                    <strong>{{ __($cat->name) }}</strong>
                                    @if($pinned->firstWhere('id', $cat->id))
                                        <span class="badge" style="background: linear-gradient(135deg, #c0392b, #1a1713); color: #fffaf1; font-size: 10px; margin-left: 6px;">{{ __('épinglé') }}</span>
                                    @else
                                        <span class="badge bg-light text-dark" style="font-size: 10px; margin-left: 6px;">{{ __('auto') }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </x-core::card.body>
            </x-core::card>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button>
                <a href="{{ route('grimba.home-rails.index') }}" class="btn btn-outline-secondary">{{ __('Annuler') }}</a>
            </div>
        </form>
    </div>
@stop
