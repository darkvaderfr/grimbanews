<section class="echo-latest-news-area home-six" @style($variableStyles)>
    <div class="hm-6-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                @if ($title = $shortcode->title)
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="hm5-feature-title">
                            <div class="hm-5-title-btn">
                                <div class="hm-5-main-title">
                                    <h2 class="title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="section-inner">
                    <div class="row">
                        @foreach($categories as $category)
                            @php $firstPost = $category->posts->first(); @endphp
                            <div class="col-xl-3 col-lg-6 col-md-6">
                                <div class="echo-latest-news-main-content">
                                    <div class="image-area">
                                        {{ RvMedia::image($category->getMetaData('image', true), $category->name, 'thumb') }}
                                    </div>
                                    <div class="content">
                                        @if ($firstPost && ($author = $firstPost->author))
                                            <p class="author">{{ __('By :name / :date', ['name' => $author->name, 'date' =>  $firstPost->created_at->format('d M Y')]) }}</p>
                                        @endif

                                        <a href="{{ $category->url }}" title="{{ $category->name }}" class="title-hover truncate-custom truncate-1-custom text-capitalize">{{ $category->name }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @if ($sidebar)
                <div class="col-xl-4 col-lg-5 col-md-12">
                    <div class="echo-feature-area-right-site-flexing echo-right-ct-1">
                        {!! dynamic_sidebar($sidebar) !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
