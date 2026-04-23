@php
    use Botble\Blog\Models\Post;

    $topNews = Post::query()
        ->where('status', 'published')
        ->where(function ($q) {
            $q->where('is_featured', false)->orWhereNull('is_featured');
        })
        ->latest()
        ->limit(6)
        ->get();
@endphp

<section class="grimba-topnews mt-4">
    <header class="grimba-topnews__head">
        <h2 class="grimba-topnews__title">Principales histoires</h2>
    </header>
    <ul class="grimba-topnews__list">
        @foreach($topNews as $p)
            <li class="grimba-topnews__item">
                <div class="grimba-topnews__body">
                    <span class="grimba-topnews__kicker">
                        @if($p->categories->first())
                            {{ $p->categories->first()->name }}
                        @endif
                        @if($p->source_name)
                            <span class="opacity-50">·</span> {{ $p->source_name }}
                        @endif
                    </span>
                    <a href="{{ $p->url }}" class="grimba-topnews__headline">{{ $p->name }}</a>
                    {!! Theme::partial('home.coverage-bar', ['post' => $p, 'compact' => false]) !!}
                </div>
                @if($p->image)
                    <a href="{{ $p->url }}" class="grimba-topnews__thumb">
                        {{ RvMedia::image($p->image, $p->name, 'thumb-medium') }}
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
</section>
