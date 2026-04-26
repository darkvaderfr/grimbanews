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

    // S184 — counts per bias for the filter tabs.
    $vaultCounts = ['all' => $count, 'left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0];
    foreach ($posts as $p) {
        $b = $p->bias_rating ?? 'unknown';
        if (! isset($vaultCounts[$b])) $b = 'unknown';
        $vaultCounts[$b]++;
    }
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
            {{-- S184 — bias filter tabs (client-side filter, no reload).
                 Only renders L/C/R buckets that have at least one post. --}}
            @if($count >= 2)
                <div class="d-flex align-items-center justify-content-center gap-1 mb-4" data-grimba-coffre-tabs role="tablist"
                     style="display:flex; border-radius:9999px; background:rgba(0,0,0,0.04); padding:4px; width:fit-content; margin-left:auto; margin-right:auto;">
                    @php
                        $tabs = [
                            'all'    => ['label' => 'Tous',   'color' => 'var(--gn-ink,#1a1713)'],
                            'left'   => ['label' => 'Gauche', 'color' => '#3b82f6'],
                            'center' => ['label' => 'Centre', 'color' => '#a8a8a8'],
                            'right'  => ['label' => 'Droite', 'color' => '#e84c3d'],
                        ];
                    @endphp
                    @foreach($tabs as $key => $meta)
                        @if($key === 'all' || ($vaultCounts[$key] ?? 0) > 0)
                            <button type="button" data-bias-tab="{{ $key }}" role="tab"
                                    aria-selected="{{ $key === 'all' ? 'true' : 'false' }}"
                                    style="
                                        padding:6px 14px; border-radius:9999px; border:none;
                                        font-weight:{{ $key === 'all' ? '700' : '600' }}; font-size:13px;
                                        background:{{ $key === 'all' ? 'var(--gn-ink,#1a1713)' : 'transparent' }};
                                        color:{{ $key === 'all' ? 'var(--gn-paper,#f6f1e8)' : 'var(--gn-ink,#1a1713)' }};
                                        cursor:pointer;
                                    ">
                                @if($key !== 'all')
                                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $meta['color'] }}; margin-right:5px; vertical-align:1px;"></span>
                                @endif
                                {{ $meta['label'] }} · {{ $vaultCounts[$key] ?? 0 }}
                            </button>
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="row g-4" data-grimba-coffre-list>
                @foreach($posts as $post)
                    <div class="col-lg-4 col-md-6 col-12" data-bias="{{ $post->bias_rating ?? 'unknown' }}">
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
        if (btn) {
            btn.addEventListener('click', () => {
                if (! confirm('Vider votre coffre ? Cette action est irréversible.')) return;
                document.cookie = 'grimba_vault=; path=/; max-age=0; SameSite=Lax';
                window.location.reload();
            });
        }

        // S184 — bias filter tabs (client-side, no reload).
        const tabs  = document.querySelectorAll('[data-grimba-coffre-tabs] [data-bias-tab]');
        const items = document.querySelectorAll('[data-grimba-coffre-list] [data-bias]');
        if (tabs.length && items.length) {
            function activate(filter) {
                tabs.forEach(t => {
                    const active = t.dataset.biasTab === filter;
                    t.setAttribute('aria-selected', String(active));
                    t.style.background = active ? 'var(--gn-ink, #1a1713)' : 'transparent';
                    t.style.color      = active ? 'var(--gn-paper, #f6f1e8)' : 'var(--gn-ink, #1a1713)';
                    t.style.fontWeight = active ? '700' : '600';
                });
                items.forEach(li => {
                    li.style.display = (filter === 'all' || li.dataset.bias === filter) ? '' : 'none';
                });
            }
            tabs.forEach(t => t.addEventListener('click', () => activate(t.dataset.biasTab)));
        }
    })();
</script>
