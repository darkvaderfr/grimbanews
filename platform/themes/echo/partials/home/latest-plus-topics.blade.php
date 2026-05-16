@php
    use App\Support\GrimbaEditorialCategories;
    use App\Support\GrimbaHomeFeed;
    use App\Support\GrimbaTranslationPresenter as GnTr;

    $latest = GrimbaHomeFeed::latest();
    GnTr::warm($latest);
    $latest->loadMissing('categories');

    $followChips = GrimbaEditorialCategories::homepageChips(8);
@endphp

<section class="grimba-latest mt-5">
    <div class="row g-4">
        <div class="col-lg-8 col-12">
            <header class="grimba-latest__head d-flex justify-content-between align-items-center mb-3">
                <h2 class="grimba-latest__title">{{ __('Dernières histoires') }}</h2>
                <a href="{{ url('/search') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">{{ __('Tout voir') }}</a>
            </header>

            <ul class="grimba-latest__list">
                @foreach($latest as $p)
                    @php
                        $title = GnTr::title($p);
                        $isTranslated = GnTr::isTranslated($p);
                        $publishedAt = GnTr::publishedAt($p);
                    @endphp
                    <li class="grimba-latest__item">
                        <div class="grimba-latest__body">
                            <span class="grimba-latest__kicker">
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
                            <a href="{{ $p->url }}" class="grimba-latest__headline">{{ $title }}</a>
                            @if($isTranslated)
                                <div class="mt-1">{!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}</div>
                            @endif
                            {!! Theme::partial('home.coverage-bar', ['post' => $p, 'compact' => true]) !!}
                        </div>
                        <a href="{{ $p->url }}" class="grimba-latest__thumb">
                            {!! Theme::partial('post-hero-img', ['post' => $p, 'size' => 'thumb-medium']) !!}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <aside class="col-lg-4 col-12">
            <header class="grimba-similar__head mb-3">
                <h2 class="h5 mb-1">{{ __('Sujets à suivre') }}</h2>
                <p class="small opacity-75 mb-0">{{ __('Constituez votre fil équilibré.') }}</p>
            </header>
            <ul class="grimba-similar__list">
                @foreach($followChips as $c)
                    <li>
                        <a class="grimba-similar__chip" href="{{ $c->url }}">
                            <span>{{ $c->name }}</span>
                            <span class="grimba-similar__plus" aria-label="{{ __('Suivre') }}">+</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
</section>
