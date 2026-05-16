@php
    use App\Support\GrimbaEditorialCategories;

    $chips = GrimbaEditorialCategories::homepageChips(20);

    $rawFollow = (string) request()->cookie('grimba_follow', '');
    $followedIds = array_filter(array_map('intval', explode(',', $rawFollow)));
    $selectedChip = (int) request()->cookie('grimba_chip', 0);
@endphp

@if($chips->isNotEmpty())
    <div class="grimba-chips" aria-label="{{ __('Sujets à suivre') }}">
        <div class="container-xxl">
            <div class="grimba-chips__rail">
                <div class="grimba-chips__row" tabindex="0" aria-label="{{ __('Catégories éditoriales') }}">
                    @foreach($chips as $chip)
                        @php
                            $isFollowed = in_array($chip->id, $followedIds, true);
                            $isSelected = (int) $chip->id === $selectedChip;
                        @endphp
                        <span class="grimba-chip @if($isFollowed) grimba-chip--followed @endif @if($isSelected) grimba-chip--selected @endif" data-category-id="{{ $chip->id }}">
                            <a class="grimba-chip__label" href="{{ $chip->url }}" data-grimba-chip-select="{{ $chip->id }}">{{ __($chip->name) }}</a>
                            <button type="button"
                                    class="grimba-chip__follow"
                                    data-grimba-follow="{{ $chip->id }}"
                                    data-label-follow="{{ __('Suivre') }}"
                                    data-label-unfollow="{{ __('Ne plus suivre') }}"
                                    aria-label="{{ ($isFollowed ? __('Ne plus suivre') : __('Suivre')) . ' ' . __($chip->name) }}">{{ $isFollowed ? '✓' : '+' }}</button>
                        </span>
                    @endforeach
                </div>
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

            const rail = document.querySelector('.grimba-chips__row');
            if (rail) {
                rail.addEventListener('wheel', (event) => {
                    const hasHorizontalIntent = Math.abs(event.deltaX) > Math.abs(event.deltaY);
                    if (hasHorizontalIntent || event.shiftKey) return;

                    const max = rail.scrollWidth - rail.clientWidth;
                    if (max <= 1) return;

                    const delta = event.deltaY;
                    const atStart = rail.scrollLeft <= 1;
                    const atEnd = rail.scrollLeft >= max - 1;
                    const canMoveHorizontally = (delta < 0 && !atStart) || (delta > 0 && !atEnd);

                    if (!canMoveHorizontally) return;

                    rail.scrollBy({ left: delta, behavior: 'auto' });
                }, { passive: true });
            }
        })();
    </script>
@endif
