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
        {{-- Vader 2026-05-16 Wave M — all blog + sub-blog listing pages
             must display at least 3 columns when the device permits,
             shrinking to 2 on tablet and 1 on phone. Sidebar variant
             caps at 3 cols (sidebar already takes 1/3 of the row);
             no-sidebar variant scales to 4 on xl+. --}}
        <div class="row g-3 g-md-4">
            @foreach($posts as $post)
                <div @class([
                    'mb-4',
                    'col-12 col-sm-6 col-lg-4' => $enableSidebar,
                    'col-12 col-sm-6 col-lg-4 col-xl-3' => ! $enableSidebar,
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
