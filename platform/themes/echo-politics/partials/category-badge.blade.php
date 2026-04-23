@php
    $post = $post ?? null;

    $category = $category ?? ($post ? $post->firstCategory : null);
@endphp

@if ($category)
    <a href="{{ $category->url }}" title="{{ $category->name }}">
        <p class="author">{{ $category->name }}</p>
    </a>
@endif
