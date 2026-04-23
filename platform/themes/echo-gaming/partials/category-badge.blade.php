@php
    $post = $post ?? null;
    $category = $category ?? ($post ? $post->firstCategory : null);
@endphp

@if ($category)
    <a href="{{ $category->url }}"><span class="content-catagory-tag truncate-custom truncate-2-custom">{{ $category->name }}</span></a>
@endif
