@if ($posts->isNotEmpty())
    @if ($sidebar == 'menu_sidebar')
        <div class="category-menu-area">
            <ul class="category-area">
                @foreach($posts as $post)
                    <li class="item">
                        <div class="image-area">
                            <a href="{{ $post->url }}">
                                {{ RvMedia::image($post->image, $post->name, 'small') }}
                            </a>
                        </div>
                        <div class="content">
                            <div class="recent-post-title">
                                <a href="{{ $post->url }}" class="truncate-custom truncate-2-custom">{{ $post->name }}</a>
                            </div>

                            @if (class_exists($post->author_type) && ($author = $post->author))
                                <p class="desc"><a href="{{ $author->url }}"><i class="fa-light fa-user"></i>{{ $author->name }}</a></p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        @switch($cardStyle)
            @case('orderly-card')
                @include(Theme::getThemeNamespace('widgets.blog-posts.templates.partials.styles.orderly-card'))
                @break
            @default
                @include(Theme::getThemeNamespace('widgets.blog-posts.templates.partials.styles.default-card'))
        @endswitch
    @endif
@endif
