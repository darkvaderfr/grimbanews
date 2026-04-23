@php
    $post = $post ?? null;
    $category = $category ?? ($post ? $post->firstCategory : null);
@endphp

@if ($category)
    <a href="{{ $category->url }}" title="{{ $category->name }}">
        <p class="author truncate-custom truncate-1-custom">{{ $category->name }} {{ $post ? ' - ' . $post->created_at->format('d M Y') : null }}</p>
    </a>
@endif
