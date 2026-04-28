@php
    Theme::set('pageTitle', $category->name);
    Theme::layout('grimba-chrome');

    $rawFollow = (string) request()->cookie('grimba_follow', '');
    $followedIds = array_filter(array_map('intval', explode(',', $rawFollow)));
    $isFollowed = in_array($category->id, $followedIds, true);

    // S138 — category-level bias distribution. Computed from the
    // last 200 published posts in this category. Reveals how the
    // topic is being covered overall — when one side dominates, the
    // bar surfaces it before the reader even scrolls.
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post;
    $catBias = Post::query()
        ->whereHas('categories', fn ($q) => $q->where('categories.id', $category->id))
        ->where('status', 'published')
        ->tap(fn ($q) => GnTr::orderForTargetLocale($q))
        ->limit(200)
        ->get(['bias_rating'])
        ->reduce(function (array $a, $p) {
            $r = $p->bias_rating ?? 'unknown';
            if (! isset($a[$r])) $a[$r] = 0;
            $a[$r]++;
            return $a;
        }, ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0]);
    $catKnown = $catBias['left'] + $catBias['center'] + $catBias['right'];
    $catTotal = $catKnown + $catBias['unknown'];
    $catPct = [
        'left'   => $catKnown ? round($catBias['left']   * 100 / $catKnown) : 0,
        'center' => $catKnown ? round($catBias['center'] * 100 / $catKnown) : 0,
        'right'  => $catKnown ? round($catBias['right']  * 100 / $catKnown) : 0,
    ];
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

        @if($catKnown > 0)
            <div class="mt-4">
                <p class="small opacity-75 mb-2">
                    <strong>Couverture sur {{ $category->name }}</strong> ·
                    {{ $catTotal }} {{ $catTotal === 1 ? 'article archivé' : 'articles archivés' }}{{ $catBias['unknown'] > 0 ? ' (' . $catBias['unknown'] . ' non classé' . ($catBias['unknown'] === 1 ? '' : 's') . ')' : '' }}
                </p>
                <div style="display:flex;height:14px;border-radius:9999px;overflow:hidden;background:rgba(0,0,0,.08);">
                    <div style="width:{{ $catPct['left'] }}%;background:#3b82f6;" title="Gauche {{ $catPct['left'] }}%"></div>
                    <div style="width:{{ $catPct['center'] }}%;background:#a8a8a8;" title="Centre {{ $catPct['center'] }}%"></div>
                    <div style="width:{{ $catPct['right'] }}%;background:#e84c3d;" title="Droite {{ $catPct['right'] }}%"></div>
                </div>
                <div class="d-flex justify-content-between small mt-2">
                    <span style="color:#3b82f6;font-weight:600;">Gauche {{ $catPct['left'] }}%</span>
                    <span style="color:#a8a8a8;font-weight:600;">Centre {{ $catPct['center'] }}%</span>
                    <span style="color:#e84c3d;font-weight:600;">Droite {{ $catPct['right'] }}%</span>
                </div>
                <p class="small opacity-60 mt-2 mb-0">
                    Distribution réelle des biais sur les 200 derniers articles de ce sujet.
                    Quand un côté domine, vous voyez immédiatement où la couverture s'écarte.
                </p>
            </div>
        @endif
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
