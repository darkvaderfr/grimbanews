@if ($post)
    <div class="hm-5-meta-box">
        @if ($category = $post->firstCategory)
            <div class="hm-5-meta-btn">
                <a href="{{ $category->url }}"><span>{{ $category->name }}</span></a>
            </div>
        @endif

        <div class="hm-5-meta-date">
            <span>{{ $post->created_at->format('d M Y') }}</span>
        </div>
    </div>
@endif
