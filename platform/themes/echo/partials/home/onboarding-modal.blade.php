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
    <div id="grimba-onboard-modal" class="grimba-newsletter-modal is-open" role="dialog" aria-labelledby="grimba-onboard-title">
        <div class="grimba-newsletter-modal__backdrop" data-grimba-onboard-close></div>
        <div class="grimba-newsletter-modal__panel glass-panel grimba-onboard-panel" role="document">
            <button type="button" class="grimba-newsletter-modal__close" aria-label="Fermer" data-grimba-onboard-close>×</button>

            <span class="grimba-methodology__kicker">Bienvenue sur GrimbaNews</span>
            <h2 id="grimba-onboard-title" class="grimba-methodology__title mt-2 mb-3">
                Voyez chaque angle de chaque histoire.
            </h2>

            <div class="row g-3 mb-4 grimba-onboard-pillars">
                <div class="col-md-4">
                    <div class="grimba-onboard-pillar">
                        <div class="grimba-onboard-pillar__bar" aria-hidden="true">
                            <span style="background:#3b82f6;"></span>
                            <span style="background:#b39152;"></span>
                            <span style="background:#ef4444;"></span>
                        </div>
                        <strong>Biais classé</strong>
                        <p class="small opacity-85 mb-0">Gauche, Centre, Droite. Chaque source, chaque article.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="grimba-onboard-pillar">
                        <div class="grimba-onboard-pillar__dot" style="background:#8a2be2;" aria-hidden="true">!</div>
                        <strong>Angles morts</strong>
                        <p class="small opacity-85 mb-0">Les histoires qu'un seul camp couvre — on les signale.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="grimba-onboard-pillar">
                        <div class="grimba-onboard-pillar__score" aria-hidden="true">92<span>/100</span></div>
                        <strong>Crédibilité</strong>
                        <p class="small opacity-85 mb-0">Score transparent par média, révisable en continu.</p>
                    </div>
                </div>
            </div>

            <div class="mb-2"><strong>Choisissez 3 sujets</strong> pour démarrer votre fil (optionnel) :</div>
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
                <span class="small opacity-75"><span id="grimba-onboard-count">0</span> sélectionné(s)</span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-grimba btn-grimba--ghost" data-grimba-onboard-skip>Passer</button>
                    <button type="button" class="btn-grimba btn-grimba--solid" data-grimba-onboard-submit>C'est parti</button>
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
            const selected = new Set();

            function refresh() {
                counter.textContent = selected.size;
                submit.textContent = selected.size === 0 ? 'Explorer sans suivre' : 'C’est parti';
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
                setTimeout(() => modal.remove(), 200);
            }

            closeBtns.forEach(b => b.addEventListener('click', async () => {
                // Still set the onboarded cookie so we don't nag.
                await fetch(@json(route('public.onboarding.complete')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ category_ids: [] })
                });
                close();
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

            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
        })();
    </script>
@endif
