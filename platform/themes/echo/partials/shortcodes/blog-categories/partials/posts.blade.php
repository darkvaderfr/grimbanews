@if ($posts->isNotEmpty())
    <div
        class="tab-pane fade active show"
        role="tabpanel" aria-labelledby="one" tabindex="0"
    >
        <div class="echo-popular-tabing-item-frist-row">
            @if ($post = $posts->first())
                <div class="row gx-5">
                    <div class="col-lg-6 col-md-6">
                        <div class="echo-popular-left-main-item one">
                            <div class="echo-popular-left-img img-transition-scale position-relative">
                                <a href="{{ $post->url }}">
                                    {{ RvMedia::image($post->image, $post->name, 'medium-square', attributes: ['class' => 'img-hover']) }}
                                </a>

                                {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="echo-popular-left-jt-text two">
                            <div class="echo-popular-area-shep">
                                <div class="home-2-area-shep secondary">
                                    <p class="text-capitalize">
                                        <a href="{{ $category->url }}" title="{{ $categoryName = $category->name }}">
                                            <span>{{ $categoryName }}</span>
                                        </a>
                                    </p>
                                </div>
                            </div>
                            <div class="echo-popular-left-jt-heading">
                                <a href="{{ $post->url }}" title="{{ $name = $post->name }}" class="title-hover truncate-3-custom truncate-custom">{{ $name }}</a>

                                @if ($description = $post->description)
                                    <p class="truncate-custom truncate-3-custom desc">{!! BaseHelper::clean($description) !!}</p>
                                @endif
                            </div>
                            <div class="home-2-read-more-btn">
                                <a href="{{ $post->url }}" class="text-uppercase df-color">{{ __('Read More') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @php
            $nextTwoPosts = $posts->skip(1)->take(2);
        @endphp

        @if ($nextTwoPosts->isNotEmpty())
            <div class="echo-popular-tabing-item-second-row">
                <div class="row gx-5">
                    @foreach($posts->skip(1)->take(2) as $post)
                        <div class="col-lg-6 col-md-6">
                            <div class="echo-popular-left-bt-contetn three">
                                <div class="echo-popular-left-bt-img">
                                    <div class="echo-popular-left-home-2-img-main img-transition-scale position-relative">
                                        <a href="{{ $post->url }}">
                                            {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                        </a>

                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                    </div>
                                    <div class="echo-popular-bt-area-shep">
                                        <div class="home-2-area-shep df-orange">
                                            <p class="text-capitalize">
                                                <a href="{{ $category->url }}">
                                                    <span>{{ $category->name }}</span>
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="echo-popular-left-bt-title">
                                    <a href="{{ $post->url }}" title="{{ $name = $post->name }}" class="title-hover truncate-2-custom truncate-custom">
                                        {{ $name }}
                                    </a>
                                    @if ($description = $post->description)
                                        <p class="text-center truncate-custom truncate-2-custom desc">
                                            {!! BaseHelper::clean($description) !!}
                                        </p>
                                    @endif
                                </div>
                                <div class="home-2-read-more-btn text-center">
                                    <a href="{{ $post->url }}" class="text-uppercase df-color">{{ __('Read More') }}</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @php
                $nextPosts = $posts->skip(3);
            @endphp

            @if ($nextPosts->isNotEmpty())
                <div class="echo-popular-item-category">
                    <div class="row flex-sm-wrap">
                        @foreach($nextPosts as $post)
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="echo-popular-cat-content">
                                    <div class="echo-popular-cat-img img-transition-scale position-relative">
                                        <a href="{{ $post->url }}">
                                            {{ RvMedia::image($post->image, $post->name, 'thumb', attributes: ['class' => 'img-hover']) }}
                                        </a>

                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                    </div>
                                    <div class="echo-popular-cat-title">
                                        <h6 class="text-capitalize">
                                            <a href="{{ $post->url }}" class="title-hover truncate-3-custom truncate-custom" title="{{ $name = $post->name }}">{{ $name }}</a>
                                        </h6>
                                        <div class="echo-popular-cat-view">
                                            {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-popular-cat-view']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
@else
    <div class="text-center" style="margin-top: 30px">{{ __('No posts') }}</div>
@endif
