@php
    if (class_exists($post->author_type)) {
        $post->loadMissing('author');
    }

    $author = class_exists($post->author_type) ?  ($post->author ?? null) : null;
    $view = $post->views ?? null;
    $content = $post->content ?? null;
    $wrapperClass = $wrapperClass ?? null;
    $isSingle = $isSingle ?? false;
    $isShowAuthorName = ($isSingle || echo_is_show_post_meta('author_name')) && $author;
    $isShowReadingTime = ($isSingle || echo_is_show_post_meta('reading_time', true)) && $post->time_reading;
    $isShowViewsCount = ($isSingle || echo_is_show_post_meta('views_count')) && $view;
@endphp

@if ($isShowAuthorName || $isShowViewsCount || $isShowReadingTime)
    <div @class(['echo-3-align-icons post-meta', $wrapperClass])>
        @if ($isShowAuthorName && $author && $author->name)
            <div class="echo-hero-area-like-read-comment-share mb-2">
                @if(is_plugin_active('member') && $author instanceof \Botble\Member\Models\Member)
                    <a href="{{ $author->url }}" title="{{ $author->name }}">
                        <x-core::icon name="ti ti-user" class="me-1" />
                        <span>{{ $author->name }}</span>
                    </a>
                @else
                    <a href="javascript:void(0)" title="{{ $author->name }}">
                        <x-core::icon name="ti ti-user" class="me-1" />
                        <span>{{ $author->name }}</span>
                    </a>
                @endif
            </div>
        @endif

        @if ($isShowReadingTime && $post->time_reading)
            <div class="echo-hero-area-like-read-comment-share mb-2">
                {!! Theme::partial('count-time-to-read', compact('post')) !!}
            </div>
        @endif

        @if ($isShowViewsCount && $view)
            <div class="echo-hero-area-like-read-comment-share mb-2">
                {!! Theme::partial('count-view', compact('view')) !!}
            </div>
        @endif

        @if ($isSingle)
            <div class="echo-hero-area-like-read-comment-share mb-2">
                <x-core::icon name="ti ti-tags" class="me-1" />
                @foreach ($post->categories as $category)
                    <a href="{{ $category->url }}" class="mx-0 px-0" title="{{ $category->name }}">
                        @if ($category->icon) <i class="{{ $category->icon }}"></i> @endif
                        <span>{{ $category->name }}</span>
                    </a>@if (!$loop->last), @endif
                @endforeach
            </div>
        @endif
    </div>
@endif
