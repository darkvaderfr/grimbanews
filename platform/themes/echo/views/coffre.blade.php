@php
    /**
     * S173 — saved-for-later vault. Cookie-only persistence (grimba_vault),
     * cap of 50, last-saved-first. No auth, no DB writes — purely a
     * client-side bookmark surface that the server hydrates with current
     * post records on render.
     *
     * @var \Illuminate\Support\Collection $posts  ordered most-recent-first
     * @var int $count
     * @var int $staleCount
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
            <span class="grimba-methodology__kicker">{{ __('Mon coffre') }}</span>
            <h1 class="grimba-methodology__title mt-2 mb-2">
                @if($count === 0)
                    {{ __("Aucun article sauvegardé pour l'instant") }}
                @else
                    {{ $count === 1 ? __(':count article sauvegardé', ['count' => $count]) : __(':count articles sauvegardés', ['count' => $count]) }}
                @endif
            </h1>
            <p class="grimba-coffre__lede mb-0">
                @if($count === 0)
                    {{ __("Cliquez sur l'étoile") }} <span aria-hidden="true">☆</span> {{ __("dans n'importe quel article pour l'ajouter ici.") }}
                    {{ __('Votre coffre reste local à votre navigateur — aucun compte requis, capacité 50 articles.') }}
                @else
                    {{ __("Vos articles sauvegardés, du plus récent au plus ancien. Cliquez sur l'étoile") }} <span aria-hidden="true">★</span> {{ __('pour retirer.') }}
                @endif
            </p>
            @if(($staleCount ?? 0) > 0)
                <div class="mt-3 small" style="padding:10px 12px; border-radius:12px; background:rgba(192,57,43,0.08); border:1px solid rgba(192,57,43,0.16); color:#8d3025;">
                    {{ $staleCount === 1 ? __(':count article indisponible retiré', ['count' => $staleCount]) : __(':count articles indisponibles retirés', ['count' => $staleCount]) }}
                </div>
            @endif
        </header>

        @if($count === 0)
            <div class="grimba-coffre__empty glass-panel p-4 p-md-5 text-center">
                <div class="grimba-coffre__empty-icon" aria-hidden="true">☆</div>
                <p class="grimba-coffre__empty-copy mb-3">
                    {{ __("Trouvez d'abord quelque chose à lire plus tard.") }}
                </p>
                <a href="{{ url('/') }}" class="btn-grimba btn-grimba--solid">
                    {{ __("Parcourir l'actualité") }}
                </a>
            </div>
        @else
            {{-- S184 — bias filter tabs (client-side filter, no reload).
                 Only renders L/C/R buckets that have at least one post. --}}
            @if($count >= 2)
                <div class="d-flex align-items-center justify-content-center gap-1 mb-4" data-grimba-coffre-tabs role="tablist" aria-label="{{ __('Filtrer le coffre par biais') }}"
                     style="display:flex; border-radius:9999px; background:rgba(0,0,0,0.04); padding:4px; width:fit-content; margin-left:auto; margin-right:auto;">
                    @php
                        $tabs = [
                            'all'    => ['label' => __('Tous'),   'color' => 'var(--gn-ink,#1a1713)'],
                            'left'   => ['label' => __('Gauche'), 'color' => '#3b82f6'],
                            'center' => ['label' => __('Centre'), 'color' => '#a8a8a8'],
                            'right'  => ['label' => __('Droite'), 'color' => '#e84c3d'],
                        ];
                    @endphp
                    @foreach($tabs as $key => $meta)
                        @if($key === 'all' || ($vaultCounts[$key] ?? 0) > 0)
                            <button type="button" data-bias-tab="{{ $key }}" role="tab"
                                    aria-controls="grimba-coffre-panel"
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

            <div class="row g-4" data-grimba-coffre-list id="grimba-coffre-panel" role="tabpanel">
                @foreach($posts as $post)
                    <div class="col-lg-4 col-md-6 col-12" data-bias="{{ $post->bias_rating ?? 'unknown' }}" data-post-id="{{ (int) $post->id }}">
                        <div class="grimba-coffre-card">
                            @include(Theme::getThemeNamespace('partials.blog.post.partials.items.grid'), ['post' => $post])
                            <div class="d-flex justify-content-between align-items-center gap-2 mt-2 px-1">
                                <span class="small opacity-60">{{ __('Retirer rapidement') }}</span>
                                <button type="button"
                                        class="btn-grimba btn-grimba--ghost btn-grimba--sm"
                                        data-grimba-vault-remove="{{ (int) $post->id }}">
                                    ✓ {{ __('Marquer comme lu') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2 mt-5">
                {{-- S182 — vault CSV export --}}
                <a href="{{ url('/coffre/export.csv') }}"
                   class="btn-grimba btn-grimba--ghost btn-grimba--sm"
                   style="padding:8px 18px; border-radius:9999px; border:1px solid rgba(26,23,19,0.2); background:transparent; color:var(--gn-ink,#1a1713); font-weight:600; font-size:13px; text-decoration:none;">
                    ⬇ {{ __('Exporter (.csv)') }}
                </a>
                <a href="{{ url('/coffre/partager') }}"
                   class="btn-grimba btn-grimba--ghost btn-grimba--sm"
                   style="padding:8px 18px; border-radius:9999px; border:1px solid rgba(26,23,19,0.2); background:transparent; color:var(--gn-ink,#1a1713); font-weight:600; font-size:13px; text-decoration:none;">
                    {{ __('Partager un lien') }}
                </a>
                <button type="button" id="grimba-coffre-clear"
                        class="btn-grimba btn-grimba--ghost btn-grimba--sm"
                        style="padding:8px 18px; border-radius:9999px; border:1px solid rgba(26,23,19,0.2); background:transparent; color:var(--gn-ink,#1a1713); font-weight:600; font-size:13px; cursor:pointer;">
                    {{ __('Vider le coffre') }}
                </button>
            </div>
        @endif
    </div>
</section>

<script>
    (function () {
        const btn = document.getElementById('grimba-coffre-clear');
        const list = document.querySelector('[data-grimba-coffre-list]');
        if (btn) {
            btn.addEventListener('click', () => {
                if (! confirm(@json(__('Vider votre coffre ? Cette action est irréversible.')))) return;
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

        document.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-grimba-vault-remove]');
            if (! remove) return;
            event.preventDefault();
            const id = String(remove.dataset.grimbaVaultRemove || '');
            const trigger = document.querySelector('[data-grimba-save="' + id + '"]');
            if (trigger && trigger.getAttribute('aria-pressed') === 'true') {
                trigger.click();
            }
        });

        document.addEventListener('grimba:vault-changed', (event) => {
            if (! list) return;
            const ids = Array.isArray(event.detail?.ids) ? event.detail.ids.map(String) : [];
            list.querySelectorAll('[data-post-id]').forEach(node => {
                node.style.display = ids.includes(node.dataset.postId) ? '' : 'none';
            });
            const visible = Array.from(list.querySelectorAll('[data-post-id]')).filter(node => node.style.display !== 'none');
            if (! visible.length) window.location.reload();
        });
    })();
</script>
