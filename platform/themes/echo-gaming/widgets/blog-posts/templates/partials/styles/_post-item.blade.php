<li>
    <div class="echo-latest-news-img img-transition-scale">
        <a href="{{ $post->url }}">
            {{ RvMedia::image($post->image, $post->url, 'thumb') }}
        </a>
    </div>
    <div class="content">
        @if ($category = $post->firstCategory)
            <a href="{{ $category->url }}"><span class="catagory-tag truncate-custom truncate-1-custom">{{ $category->name }}</span></a>
        @endif
            <a href="{{ $post->url }}" title="{{ $post->name }}" class="text-capitalize title-hover title truncate-custom truncate-2-custom text-heading">{{ $post->name }}</a>
            <p class="desc"><i class="fa-solid fa-calendar"></i>{{ $post->created_at->format('d M Y') }}</p>
    </div>
</li>
