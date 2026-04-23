@php
    Theme::set('pageTitle', $category->name);
    Theme::layout('grimba-chrome');

    $rawFollow = (string) request()->cookie('grimba_follow', '');
    $followedIds = array_filter(array_map('intval', explode(',', $rawFollow)));
    $isFollowed = in_array($category->id, $followedIds, true);
@endphp

<section class="grimba-category-hero container">
    <header class="glass-panel p-4 p-md-5 mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <span class="grimba-methodology__kicker">Sujet</span>
                <h1 class="grimba-methodology__title mt-2 mb-2">{{ $category->name }}</h1>
                @if($category->description)
                    <p class="mb-0 opacity-85" style="max-width: 62ch;">
                        {!! \Illuminate\Support\Str::limit(strip_tags($category->description), 260) !!}
                    </p>
                @else
                    <p class="mb-0 opacity-85" style="max-width: 62ch;">
                        Toutes les histoires classées dans <strong>{{ $category->name }}</strong>, côté à côté avec leurs biais éditoriaux et sources.
                    </p>
                @endif
            </div>

            <button type="button"
                    class="btn-grimba {{ $isFollowed ? 'btn-grimba--solid' : 'btn-grimba--ghost' }}"
                    data-grimba-follow="{{ $category->id }}"
                    data-grimba-category-hero>
                <span class="grimba-category-hero__glyph">{{ $isFollowed ? '✓' : '+' }}</span>
                <span>{{ $isFollowed ? 'Suivi' : 'Suivre ce sujet' }}</span>
            </button>
        </div>
    </header>
</section>

@include(Theme::getThemeNamespace('views.loop'))

<script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const btn = document.querySelector('[data-grimba-category-hero]');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            const id = btn.dataset.grimbaFollow;
            const res = await fetch(@json(route('public.topics.follow')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ category_id: id, action: 'toggle' })
            }).then(r => r.json()).catch(() => null);

            if (!res || !res.ok) return;

            const nowFollowed = res.followed.includes(parseInt(id));
            btn.classList.toggle('btn-grimba--solid', nowFollowed);
            btn.classList.toggle('btn-grimba--ghost', ! nowFollowed);
            btn.querySelector('.grimba-category-hero__glyph').textContent = nowFollowed ? '✓' : '+';
            btn.querySelector('span:last-child').textContent = nowFollowed ? 'Suivi' : 'Suivre ce sujet';

            // Sync the matching chip + counter.
            const chip = document.querySelector(`.grimba-chip[data-category-id="${id}"]`);
            if (chip) {
                chip.classList.toggle('grimba-chip--followed', nowFollowed);
                const chipBtn = chip.querySelector('[data-grimba-follow]');
                if (chipBtn) chipBtn.textContent = nowFollowed ? '✓' : '+';
            }
            const counter = document.getElementById('grimba-follow-count');
            if (counter) counter.textContent = String(res.count);
        });
    })();
</script>
