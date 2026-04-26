@php
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post;
    use Illuminate\Support\Facades\DB;

    $balancedClusters = DB::table('posts')
        ->select('story_cluster_id')
        ->where('status', 'published')
        ->whereNotNull('story_cluster_id')
        ->whereIn('bias_rating', ['left', 'center', 'right'])
        ->groupBy('story_cluster_id')
        ->havingRaw('COUNT(DISTINCT bias_rating) >= 2')
        ->pluck('story_cluster_id');

    // Principales histoires: prefer clustered posts (so the L/C/R bar
    // actually draws) then pad with latest published.
    $clustered = Post::query()
        ->where('status', 'published')
        ->where(function ($q) {
            $q->where('is_featured', false)->orWhereNull('is_featured');
        })
        ->whereIn('story_cluster_id', $balancedClusters)
        ->latest()
        ->limit(6)
        ->get();

    if ($clustered->count() < 6) {
        $pad = Post::query()
            ->where('status', 'published')
            ->whereNotIn('id', $clustered->pluck('id'))
            ->latest()
            ->limit(6 - $clustered->count())
            ->get();
        $topNews = $clustered->concat($pad);
    } else {
        $topNews = $clustered;
    }
@endphp

<section class="grimba-topnews mt-4">
    <header class="grimba-topnews__head">
        <h2 class="grimba-topnews__title">Principales histoires</h2>
    </header>
    <ul class="grimba-topnews__list">
        @foreach($topNews as $p)
            @php
                $title = GnTr::title($p);
                $isTranslated = GnTr::isTranslated($p);
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
                        @if($p->created_at)
                            <span class="opacity-50">·</span> {{ $p->created_at->locale('fr')->diffForHumans(['short' => false]) }}
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
