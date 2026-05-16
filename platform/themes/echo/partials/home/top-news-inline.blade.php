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
                $__tnBias = $p->bias_rating ?? null;
                $__tnBiasColor = match ($__tnBias) {
                    'left' => '#3b82f6',
                    'center' => '#a8a8a8',
                    'right' => '#e84c3d',
                    default => null,
                };
            @endphp
            <li class="grimba-topnews__item grimba-topnews__item--striped"
                @if($__tnBiasColor) style="--gn-tn-bias: {{ $__tnBiasColor }};" @endif>
                @if($__tnBiasColor)
                    <span class="grimba-topnews__stripe" aria-hidden="true"></span>
                @endif
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

<style>
    .grimba-topnews__item--striped {
        position: relative;
        padding-left: 18px;
        transition: transform .25s cubic-bezier(.22, 1, .36, 1);
    }

    .grimba-topnews__item--striped:hover {
        transform: translateX(2px);
    }

    .grimba-topnews__stripe {
        position: absolute;
        left: 4px;
        top: 14px;
        bottom: 14px;
        width: 3px;
        border-radius: 999px;
        background: var(--gn-tn-bias, #a8a8a8);
        box-shadow: 0 0 0 1px rgba(255, 255, 255, .35);
        transition: box-shadow .25s ease, width .25s ease;
    }

    .grimba-topnews__item--striped:hover .grimba-topnews__stripe {
        width: 5px;
        box-shadow:
            0 0 0 1px rgba(255, 255, 255, .35),
            0 0 12px color-mix(in srgb, var(--gn-tn-bias, #a8a8a8) 60%, transparent);
    }

    [data-bs-theme="dark"] .grimba-topnews__stripe,
    body[data-theme="dark"] .grimba-topnews__stripe {
        box-shadow: 0 0 0 1px rgba(0, 0, 0, .35);
    }

    @media (prefers-reduced-motion: reduce) {
        .grimba-topnews__item--striped,
        .grimba-topnews__stripe {
            transition: none;
        }
    }
</style>
