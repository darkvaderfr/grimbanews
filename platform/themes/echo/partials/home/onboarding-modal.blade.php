@php
    use Botble\Blog\Models\Category;

    $onboarded  = request()->cookie('grimba_onboarded') === '1';
    $rawFollow  = (string) request()->cookie('grimba_follow', '');
    $existingFollows = array_filter(array_map('intval', explode(',', $rawFollow)));

    // Skip onboarding when the user has already interacted (cookie set)
    // OR already follows any topic (silent re-onboard avoided).
    $skip = $onboarded || ! empty($existingFollows);

    $topics = Category::query()
        ->where('status', 'published')
        ->orderBy('order')
        ->limit(12)
        ->get();
@endphp

@if(! $skip)
    <div id="grimba-onboard-modal" class="grimba-newsletter-modal is-open" role="dialog" aria-modal="true" aria-labelledby="grimba-onboard-title">
        <div class="grimba-newsletter-modal__backdrop" data-grimba-onboard-close></div>
        <div class="grimba-newsletter-modal__panel glass-panel grimba-onboard-panel" role="document">
            <button type="button" class="grimba-newsletter-modal__close" aria-label="{{ __('Fermer') }}" data-grimba-onboard-close>×</button>

            <span class="grimba-methodology__kicker">{{ __('Bienvenue sur GrimbaNews') }}</span>
            <h2 id="grimba-onboard-title" class="grimba-methodology__title mt-2 mb-3">
                {{ __('Voyez chaque angle de chaque histoire.') }}
            </h2>

            <div class="row g-3 mb-4 grimba-onboard-pillars">
                <div class="col-md-4">
                    <div class="grimba-onboard-pillar">
                        <div class="grimba-onboard-pillar__bar" aria-hidden="true">
                            <span style="background:#3b82f6;"></span>
                            <span style="background:#a8a8a8;"></span>
                            <span style="background:#ef4444;"></span>
                        </div>
                        <strong>{{ __('Biais classé') }}</strong>
                        <p class="small opacity-85 mb-0">{{ __('Gauche, Centre, Droite. Chaque source, chaque article.') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="grimba-onboard-pillar">
                        <div class="grimba-onboard-pillar__dot" style="background:#c0392b;" aria-hidden="true">!</div>
                        <strong>{{ __('Angles morts') }}</strong>
                        <p class="small opacity-85 mb-0">{{ __("Les histoires qu'un seul camp couvre — on les signale.") }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="grimba-onboard-pillar">
                        <div class="grimba-onboard-pillar__score" aria-hidden="true">92<span>/100</span></div>
                        <strong>{{ __('Crédibilité') }}</strong>
                        <p class="small opacity-85 mb-0">{{ __('Score transparent par média, révisable en continu.') }}</p>
                    </div>
                </div>
            </div>

            <div class="mb-2"><strong>{{ __('Choisissez 3 sujets') }}</strong> {{ __('pour démarrer votre fil (optionnel) :') }}</div>
            <p class="small opacity-75 mb-3">
                {{ __("Astuce : l'étoile") }} <span aria-hidden="true">★</span> {{ __("sauvegarde n'importe quel article dans votre coffre pour plus tard, sans créer de compte.") }}
            </p>
            <div class="d-flex flex-wrap gap-2 mb-3 grimba-onboard-topics">
                @foreach($topics as $topic)
                    <button type="button"
                            class="btn-grimba btn-grimba--ghost btn-grimba--sm grimba-onboard-topic"
                            data-topic-id="{{ $topic->id }}"
                            aria-pressed="false">
                        {{ $topic->name }}
                    </button>
                @endforeach
            </div>

            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <span class="small opacity-75"><span id="grimba-onboard-count">0</span> {{ __('sélectionné(s)') }}</span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-grimba btn-grimba--ghost" data-grimba-onboard-skip>{{ __('Passer') }}</button>
                    <button type="button" class="btn-grimba btn-grimba--solid" data-grimba-onboard-submit>{{ __("C'est parti") }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal    = document.getElementById('grimba-onboard-modal');
            const topics   = modal.querySelectorAll('.grimba-onboard-topic');
            const counter  = document.getElementById('grimba-onboard-count');
            const closeBtns = modal.querySelectorAll('[data-grimba-onboard-close], [data-grimba-onboard-skip]');
            const submit    = modal.querySelector('[data-grimba-onboard-submit]');
            const csrf      = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
            const selected = new Set();
            const emptyLabel = @json(__('Explorer sans suivre'));
            const readyLabel = @json(__("C'est parti"));

            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                (topics[0] || submit || modal.querySelector(focusableSelector))?.focus();
            });

            function focusables() {
                return Array.from(modal.querySelectorAll(focusableSelector)).filter(el => el.offsetParent !== null);
            }

            function refresh() {
                counter.textContent = selected.size;
                submit.textContent = selected.size === 0 ? emptyLabel : readyLabel;
            }

            topics.forEach(btn => btn.addEventListener('click', () => {
                const id = btn.dataset.topicId;
                const active = !selected.has(id);
                if (active) selected.add(id); else selected.delete(id);
                btn.setAttribute('aria-pressed', active);
                btn.classList.toggle('btn-grimba--solid', active);
                btn.classList.toggle('btn-grimba--ghost', !active);
                refresh();
            }));

            function close() {
                modal.classList.remove('is-open');
                document.body.style.overflow = '';
                setTimeout(() => modal.remove(), 200);
            }

            closeBtns.forEach(b => b.addEventListener('click', async () => {
                try {
                    // Still set the onboarded cookie so we don't nag.
                    await fetch(@json(route('public.onboarding.complete')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ category_ids: [] })
                    });
                } finally {
                    close();
                }
            }));

            submit.addEventListener('click', async () => {
                const ids = Array.from(selected);
                const res = await fetch(@json(route('public.onboarding.complete')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ category_ids: ids })
                }).then(r => r.json()).catch(() => null);

                if (res && res.ok) {
                    // Reload so the chips + counter reflect the new follow state.
                    window.location.reload();
                } else {
                    close();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (! document.body.contains(modal) || ! modal.classList.contains('is-open')) return;
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closeBtns[0]?.click();
                    return;
                }
                if (e.key !== 'Tab') return;

                const nodes = focusables();
                if (! nodes.length) return;
                const first = nodes[0];
                const last = nodes[nodes.length - 1];

                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (! e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            });
        })();
    </script>
@endif
