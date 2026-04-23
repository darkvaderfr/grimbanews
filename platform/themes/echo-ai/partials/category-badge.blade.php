@php
    $post = $post ?? null;

    $category = $category ?? ($post ? $post->firstCategory : null);
@endphp

@if ($category)
    <a href="{{ $category->url }}" title="{{ $category->name }}" class="truncate-custom truncate-1-custom category-badge">
        <span>{{ $category->name }}</span>
    </a>
@endif
