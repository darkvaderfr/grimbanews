<div class="row">
    @if ($title = Arr::get($config, 'title'))
        <div class="col-lg-12">
            <div class="title-area">
                <div class="right-side-title">
                    <h6 class="sect-title">{!! BaseHelper::clean($title) !!}</h6>
                </div>
            </div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="echo-feature-right-area">
            @if ($firstPost = $posts->first())
                @php
                    $image = $firstPost->image ? RvMedia::getImageUrl($firstPost->image) : null;
                @endphp
                <div class="echo-feature-area-last-content">
                    <div class="echo-feature-area-last-content-text"
                        @style([
                            "--background-image: url('$image')" => $image,
                            "background-color: #5E5E5E !important" => ! $image,
                        ])
                    >
                        {!! Theme::partial('category-badge', ['post' => $firstPost]) !!}

                        <h3 class="text-capitalize">
                            <a href="{{ $firstPost->url }}" title="{{ $firstPost->name }}" class="title-hover truncate-custom truncate-2-custom">
                                {!! BaseHelper::clean($firstPost->name) !!}
                            </a>
                        </h3>
                        {!! Theme::partial('post-meta', ['post' => $firstPost]) !!}
                    </div>
                </div>
            @endif

            @if ($posts = $posts->skip(1))
                <ul class="echo-right-side-small-content">
                    @foreach($posts as $post)
                        <li class="content-inner">
                            <div class="number">
                                @php
                                    $index = $loop->iteration + 1;
                                @endphp
                                <h3>{{ $index < 10 ? '0' . $index : $index }}</h3>
                            </div>
                            <div class="content">
                                <h3 class="text-capitalize"><a href="{{ $post->url }}" class="title-hover truncate-2-custom truncate-custom">{{ $post->name }}</a></h3>
                                {!! Theme::partial('post-meta', compact('post')) !!}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
