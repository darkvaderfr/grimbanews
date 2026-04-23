@php
    $enableSidebar = $enableSidebar ?? theme_option('blog_sidebar_enabled', true);
    $postStyle = isset($postStyle) && in_array($postStyle, ['card', 'grid', 'list', 'mixed']) ? $postStyle :  theme_option('post_style', 'card');
@endphp


{!! apply_filters('ads_render', null, 'post_list_before', ['class' => 'my-2 text-center']) !!}

@switch($postStyle)
    @case('mixed')
        {!! Theme::partial('blog.post-mixed', compact('posts')) !!}
    @break

    @case('grid')
        <div class="row">
            @foreach($posts as $post)
                <div @class([
                    'mb-4',
                    'col-md-6' => $enableSidebar,
                    'col-md-6 col-lg-4' => ! $enableSidebar,
                ])>
                    {!! Theme::partial('blog.post.item', compact('post', 'postStyle')) !!}
                </div>
            @endforeach
        </div>
    @break

    @default
        @foreach($posts as $post)
            {!! Theme::partial('blog.post.item', compact('post', 'postStyle')) !!}
        @endforeach
@endswitch

@if ($posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $posts->total() > 0)
    <div class="text-center mt-30">
        {{ $posts->withQueryString()->links(Theme::getThemeNamespace('partials.pagination')) }}
    </div>
@endif

{!! apply_filters('ads_render', null, 'post_list_after', ['class' => 'my-2 text-center']) !!}
