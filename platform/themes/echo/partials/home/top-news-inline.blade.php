@php
    use App\Support\GrimbaHomeFeed;
    use App\Support\GrimbaTranslationPresenter as GnTr;

    $topNews = GrimbaHomeFeed::topNews();
    GnTr::warm($topNews);
@endphp

<section class="grimba-topnews mt-4">
    <header class="grimba-topnews__head">
        <h2 class="grimba-topnews__title">{{ __('Principales histoires') }}</h2>
    </header>
    <ul class="grimba-topnews__list">
        @foreach($topNews as $p)
            @php
                $title = GnTr::title($p);
                $isTranslated = GnTr::isTranslated($p);
                $publishedAt = GnTr::publishedAt($p);
            @endphp
            <li class="grimba-topnews__item">
                <div class="grimba-topnews__body">
                    <span class="grimba-topnews__kicker">
                        @if($p->categories->first())
                            {{ $p->categories->first()->name }}
                        @endif
                        @if($p->source_name)
                            <span class="opacity-50">·</span> {{ $p->source_name }}
                        @endif
                        @if($publishedAt)
                            <span class="opacity-50">·</span> {{ $publishedAt->locale('fr')->diffForHumans(['short' => false]) }}
                        @endif
                    </span>
                    <a href="{{ $p->url }}" class="grimba-topnews__headline">{{ $title }}</a>
                    @if($isTranslated)
                        <div class="mt-1">{!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}</div>
                    @endif
                    {!! Theme::partial('home.coverage-bar', ['post' => $p, 'compact' => false]) !!}
                </div>
                <a href="{{ $p->url }}" class="grimba-topnews__thumb">
                    {!! Theme::partial('post-hero-img', ['post' => $p, 'size' => 'thumb-medium']) !!}
                </a>
            </li>
        @endforeach
    </ul>
</section>
