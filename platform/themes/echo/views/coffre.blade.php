@php
    /**
     * S173 — saved-for-later vault. Cookie-only persistence (grimba_vault),
     * cap of 50, last-saved-first. No auth, no DB writes — purely a
     * client-side bookmark surface that the server hydrates with current
     * post records on render.
     *
     * @var \Illuminate\Support\Collection $posts  ordered most-recent-first
     * @var int $count
     */
    Theme::layout('grimba-chrome');
@endphp

<section class="grimba-coffre py-5">
    <div class="container">

        <header class="glass-panel p-4 p-md-5 mb-4">
            <span class="grimba-methodology__kicker">Mon coffre</span>
            <h1 class="grimba-methodology__title mt-2 mb-2">
                @if($count === 0)
                    Aucun article sauvegardé pour l'instant
                @else
                    {{ $count }} {{ $count === 1 ? 'article sauvegardé' : 'articles sauvegardés' }}
                @endif
            </h1>
            <p class="mb-0 opacity-85">
                @if($count === 0)
                    Cliquez sur l'étoile <span aria-hidden="true">☆</span> dans n'importe quel article pour l'ajouter ici.
                    Votre coffre reste local à votre navigateur — aucun compte requis, capacité 50 articles.
                @else
                    Vos articles sauvegardés, du plus récent au plus ancien. Cliquez sur l'étoile <span aria-hidden="true">★</span> pour retirer.
                @endif
            </p>
        </header>

        @if($count === 0)
            <div class="glass-panel p-4 p-md-5 text-center">
                <div style="font-size:48px; line-height:1; margin-bottom:14px; opacity:0.4;">☆</div>
                <p class="mb-3 opacity-85" style="font-size:16px;">
                    Trouvez d'abord quelque chose à lire plus tard.
                </p>
                <a href="{{ url('/') }}" class="btn-grimba btn-grimba--solid">
                    Parcourir l'actualité
                </a>
            </div>
        @else
            <div class="row g-4">
                @foreach($posts as $post)
                    <div class="col-lg-4 col-md-6 col-12">
                        @include(Theme::getThemeNamespace('partials.blog.post.partials.items.grid'), ['post' => $post])
                    </div>
                @endforeach
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2 mt-5">
                {{-- S182 — vault CSV export --}}
                <a href="{{ url('/coffre/export.csv') }}"
                   class="btn-grimba btn-grimba--ghost btn-grimba--sm"
                   style="padding:8px 18px; border-radius:9999px; border:1px solid rgba(26,23,19,0.2); background:transparent; color:var(--gn-ink,#1a1713); font-weight:600; font-size:13px; text-decoration:none;">
                    ⬇ Exporter (.csv)
                </a>
                <button type="button" id="grimba-coffre-clear"
                        class="btn-grimba btn-grimba--ghost btn-grimba--sm"
                        style="padding:8px 18px; border-radius:9999px; border:1px solid rgba(26,23,19,0.2); background:transparent; color:var(--gn-ink,#1a1713); font-weight:600; font-size:13px; cursor:pointer;">
                    Vider le coffre
                </button>
            </div>
        @endif
    </div>
</section>

<script>
    (function () {
        const btn = document.getElementById('grimba-coffre-clear');
        if (! btn) return;
        btn.addEventListener('click', () => {
            if (! confirm('Vider votre coffre ? Cette action est irréversible.')) return;
            document.cookie = 'grimba_vault=; path=/; max-age=0; SameSite=Lax';
            window.location.reload();
        });
    })();
</script>
