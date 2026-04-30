@php
    use Botble\Blog\Models\Category;

    $chips = Category::query()
        ->where('status', 'published')
        ->whereIn('name', ['Afrique', 'International'])
        ->orderBy('order')
        ->limit(2)
        ->get();

    $rawFollow = (string) request()->cookie('grimba_follow', '');
    $followedIds = array_filter(array_map('intval', explode(',', $rawFollow)));
    $selectedChip = (int) request()->cookie('grimba_chip', 0);
@endphp

@if($chips->isNotEmpty())
    <div class="grimba-chips" aria-label="{{ __('Sujets à suivre') }}">
        <div class="container-xxl">
            <div class="grimba-chips__row">
                @foreach($chips as $chip)
                    @php
                        $isFollowed = in_array($chip->id, $followedIds, true);
                        $isSelected = (int) $chip->id === $selectedChip;
                    @endphp
                    <span class="grimba-chip @if($isFollowed) grimba-chip--followed @endif @if($isSelected) grimba-chip--selected @endif" data-category-id="{{ $chip->id }}">
                        <a class="grimba-chip__label" href="{{ $chip->url }}" data-grimba-chip-select="{{ $chip->id }}">{{ $chip->name }}</a>
                        <button type="button"
                                class="grimba-chip__follow"
                                data-grimba-follow="{{ $chip->id }}"
                                data-label-follow="{{ __('Suivre') }}"
                                data-label-unfollow="{{ __('Ne plus suivre') }}"
                                aria-label="{{ ($isFollowed ? __('Ne plus suivre') : __('Suivre')) . ' ' . $chip->name }}">{{ $isFollowed ? '✓' : '+' }}</button>
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            document.querySelectorAll('[data-grimba-follow]').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const id = btn.dataset.grimbaFollow;
                    const res = await fetch(@json(route('public.topics.follow')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ category_id: id, action: 'toggle' })
                    }).then(r => r.json()).catch(() => null);

                    if (!res || !res.ok) return;
                    const chip = btn.closest('.grimba-chip');
                    const nowFollowed = res.followed.includes(parseInt(id));
                    chip.classList.toggle('grimba-chip--followed', nowFollowed);
                    btn.textContent = nowFollowed ? '✓' : '+';
                    btn.setAttribute('aria-label', nowFollowed ? btn.dataset.labelUnfollow : btn.dataset.labelFollow);
                    // Update counter in header meta strip if present.
                    const counter = document.getElementById('grimba-follow-count');
                    if (counter) counter.textContent = String(res.count);
                });
            });
            document.querySelectorAll('[data-grimba-chip-select]').forEach(link => {
                link.addEventListener('click', () => {
                    document.cookie = 'grimba_chip=' + encodeURIComponent(link.dataset.grimbaChipSelect)
                        + '; path=/; max-age=' + (60 * 60 * 24 * 365) + '; SameSite=Lax';
                });
            });
        })();
    </script>
@endif
