@php
    Theme::layout('grimba-chrome');

    /**
     * S188 — vault share/import bridge.
     *
     * @var string $mode
     * @var int $count
     * @var string $shareUrl
     */
@endphp

<section class="grimba-coffre-share py-5">
    <div class="container" style="max-width:860px;">
        <header class="glass-panel p-4 p-md-5 text-center">
            <span class="grimba-methodology__kicker">Coffre local</span>

            @if($mode === 'share')
                <h1 class="grimba-methodology__title mt-2 mb-3">Partager votre sélection</h1>
                @if($count === 0)
                    <p class="opacity-80 mb-4">
                        Votre coffre est vide. Sauvegardez quelques articles avec l'étoile, puis revenez créer un lien.
                    </p>
                    <a href="{{ url('/') }}" class="btn-grimba btn-grimba--solid">Retour à l'accueil</a>
                @else
                    <p class="opacity-80 mb-4">
                        Ce lien contient {{ $count }} {{ $count === 1 ? 'article' : 'articles' }} dans le fragment du navigateur.
                        Rien n'est enregistré sur le serveur.
                    </p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <button type="button"
                                class="btn-grimba btn-grimba--solid"
                                data-grimba-copy-link="{{ $shareUrl }}">
                            Copier le lien
                        </button>
                        <a href="{{ $shareUrl }}" class="btn-grimba btn-grimba--ghost">
                            Tester l'import
                        </a>
                    </div>
                    <p class="small opacity-60 mt-3 mb-0" style="word-break:break-all;">{{ $shareUrl }}</p>
                @endif
            @else
                <h1 class="grimba-methodology__title mt-2 mb-3">Importer un coffre partagé</h1>
                <p class="opacity-80 mb-4" data-grimba-vault-import-status>
                    Lecture du lien partagé...
                </p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <button type="button" class="btn-grimba btn-grimba--solid" data-grimba-vault-import hidden>
                        Importer dans mon coffre
                    </button>
                    <a href="{{ url('/coffre') }}" class="btn-grimba btn-grimba--ghost">Voir mon coffre</a>
                </div>
            @endif
        </header>
    </div>
</section>

@if($mode === 'import')
    <script>
        (function () {
            const status = document.querySelector('[data-grimba-vault-import-status]');
            const button = document.querySelector('[data-grimba-vault-import]');
            const params = new URLSearchParams((window.location.hash || '').replace(/^#/, ''));
            const raw = params.get('ids') || '';
            const ids = raw.split(',')
                .map(value => parseInt(value, 10))
                .filter(Number.isFinite)
                .filter(value => value > 0)
                .filter((value, index, arr) => arr.indexOf(value) === index)
                .slice(0, 50);

            if (! ids.length) {
                if (status) status.textContent = "Ce lien ne contient aucun article importable.";
                return;
            }

            if (status) {
                status.textContent = ids.length + ' ' + (ids.length === 1 ? 'article proposé' : 'articles proposés') + ' dans ce lien.';
            }
            if (! button) return;

            button.hidden = false;
            button.addEventListener('click', () => {
                if (! confirm('Remplacer votre coffre local par cette sélection partagée ?')) return;
                document.cookie = 'grimba_vault=' + encodeURIComponent(ids.join(',')) + '; path=/; max-age=' + (60 * 60 * 24 * 365) + '; SameSite=Lax';
                window.location.href = '{{ url('/coffre') }}';
            });
        })();
    </script>
@endif
