@php
    use App\Support\GrimbaEditorialCategories;

    $post = $post ?? null;
    $limit = max(1, (int) ($limit ?? 2));
    $editionNames = GrimbaEditorialCategories::editionNames();
    $topicNames = GrimbaEditorialCategories::topicNames();

    $category = $category ?? ($post ? $post->firstCategory : null);
    $categories = collect();

    if ($post) {
        $post->loadMissing('categories');
        $postCategories = $post->categories;

        $topic = $postCategories
            ->filter(fn ($item): bool => in_array($item->name, $topicNames, true))
            ->sortBy('order')
            ->first();
        $edition = $postCategories
            ->filter(fn ($item): bool => in_array($item->name, $editionNames, true))
            ->sortBy('order')
            ->first();
        $fallback = $postCategories
            ->reject(fn ($item): bool => in_array($item->id, array_filter([$topic?->id, $edition?->id]), true))
            ->sortBy('order');

        $categories = collect([$topic, $edition])
            ->filter()
            ->concat($fallback)
            ->unique('id')
            ->take($limit)
            ->values();
    } elseif ($category) {
        $categories = collect([$category]);
    }
@endphp

@if ($categories->isNotEmpty())
    <span class="grimba-category-badges" aria-label="{{ __('Catégories') }}">
        @foreach($categories as $badgeCategory)
            @php
                $role = in_array($badgeCategory->name, $editionNames, true)
                    ? 'edition'
                    : (in_array($badgeCategory->name, $topicNames, true) ? 'topic' : 'category');
            @endphp
            <a title="{{ $badgeCategory->name }}"
               href="{{ $badgeCategory->url }}"
               class="grimba-category-badge grimba-category-badge--{{ $role }}"
               data-grimba-category-role="{{ $role }}">
                @if ($badgeCategory->icon)
                    <i class="{{ $badgeCategory->icon }}"></i>
                @endif
                <span class="content-catagory-tag">{{ $badgeCategory->name }}</span>
            </a>
        @endforeach
    </span>
@endif
