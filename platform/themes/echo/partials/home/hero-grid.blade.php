@php
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post;
    use Illuminate\Support\Facades\DB;

    // Clusters that actually have ≥2 bias sides — only those unlock the L/C/R bar.
    $balancedClusters = DB::table('posts')
        ->select('story_cluster_id')
        ->where('status', 'published')
        ->whereNotNull('story_cluster_id')
        ->whereIn('bias_rating', ['left', 'center', 'right'])
        ->groupBy('story_cluster_id')
        ->havingRaw('COUNT(DISTINCT bias_rating) >= 2')
        ->pluck('story_cluster_id');

    // Hero = most recent featured post that's part of a balanced cluster
    // AND has a real hero image (RSS-sourced external URL beats the dark
    // gradient placeholder SVGs the seeds use). Falls back to:
    //   featured + real-image (any cluster) → featured (any image) →
    //   published + real-image → latest published.
    $realImageFilter = fn ($q) => $q->where('image', 'like', 'http%');

    $hero = Post::query()->where('status', 'published')->where('is_featured', true)
            ->whereIn('story_cluster_id', $balancedClusters)
            ->tap($realImageFilter)
            ->latest()->first()
        ?? Post::query()->where('status', 'published')->where('is_featured', true)
            ->tap($realImageFilter)->latest()->first()
        ?? Post::query()->where('status', 'published')->where('is_featured', true)
            ->whereIn('story_cluster_id', $balancedClusters)
            ->latest()->first()
        ?? Post::query()->where('status', 'published')->where('is_featured', true)->latest()->first()
        ?? Post::query()->where('status', 'published')->tap($realImageFilter)->latest()->first()
        ?? Post::query()->where('status', 'published')->latest()->first();

    // Briefing prefers clustered posts (bar draws under each), then fills
    // with non-clustered recents so the column is always 5 items.
    $clusteredRecent = Post::query()
        ->where('status', 'published')
        ->whereIn('story_cluster_id', $balancedClusters)
        ->when($hero, fn ($q) => $q->where('id', '!=', $hero->id))
        ->latest()
        ->limit(5)
        ->get();

    $briefing = $clusteredRecent;
    if ($briefing->count() < 5) {
        $fill = Post::query()
            ->where('status', 'published')
            ->whereNotIn('id', $briefing->pluck('id')->push($hero?->id)->filter())
            ->latest()
            ->limit(5 - $briefing->count())
            ->get();
        $briefing = $briefing->concat($fill);
    }

    $blindspots = Post::query()
        ->where('status', 'published')
        ->where('is_blindspot', true)
        ->latest()
        ->limit(2)
        ->get();

    // Counter values — full published recent set, not just what we render.
    $briefingStats = Post::query()->where('status', 'published')->latest()->limit(9)->get();

    $totalArticles = $briefingStats->sum(fn ($p) => max(1, $p->views ?? 1));
    $readMinutes   = max(1, (int) round($briefingStats->count() * 1.2));
@endphp

<section class="grimba-hero-grid">
    <div class="row g-4">

        {{-- Left: Daily Briefing --}}
        <aside class="col-xl-3 col-lg-4 col-12 grimba-briefing">
            <header class="grimba-briefing__head">
                <h2 class="grimba-briefing__title">Briefing du jour</h2>
                <p class="grimba-briefing__sub">
                    {{ $briefingStats->count() }} histoires · {{ $totalArticles }} articles · {{ $readMinutes }} min de lecture
                </p>
            </header>

            <ol class="grimba-briefing__list">
                @foreach($briefing->take(5) as $p)
                    @php
                        $title = GnTr::title($p);
                        $isTranslated = GnTr::isTranslated($p);
                    @endphp
                    <li class="grimba-briefing__item">
                        <a href="{{ $p->url }}" class="grimba-briefing__thumb">
                            {!! Theme::partial('post-hero-img', ['post' => $p, 'size' => 'small']) !!}
                        </a>
                        <div class="grimba-briefing__body">
                            <a href="{{ $p->url }}" class="grimba-briefing__headline">{{ $title }}</a>
                            @if($isTranslated)
                                {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
                            @endif
                            @if($p->created_at)
                                <span class="small opacity-75">{{ $p->created_at->locale('fr')->diffForHumans(['short' => false]) }}</span>
                            @endif
                            {!! Theme::partial('home.coverage-bar', ['post' => $p, 'compact' => true]) !!}
                        </div>
                    </li>
                @endforeach
            </ol>

            <a href="{{ url('/blog') }}" class="grimba-briefing__more">
                Voir le briefing complet →
            </a>
        </aside>

        {{-- Center: Hero Story --}}
        <section class="col-xl-6 col-lg-8 col-12 grimba-hero">
            @if($hero)
                @php
                    $heroTitle = GnTr::title($hero);
                    $heroDesc = GnTr::description($hero);
                    $heroTranslated = GnTr::isTranslated($hero);
                @endphp
                <a href="{{ $hero->url }}" class="grimba-hero__media">
                    {!! Theme::partial('post-hero-img', ['post' => $hero, 'size' => 'extra-large']) !!}
                    <div class="grimba-hero__gradient"></div>
                    <div class="grimba-hero__text">
                        <h1 class="grimba-hero__title">{{ $heroTitle }}</h1>
                        @if($heroTranslated)
                            {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
                        @endif
                        @if($heroDesc)
                            <p class="grimba-hero__desc">{{ \Illuminate\Support\Str::limit(strip_tags($heroDesc), 140) }}</p>
                        @endif
                    </div>
                    <div class="grimba-hero__coverage">
                        {!! Theme::partial('home.coverage-bar', ['post' => $hero, 'compact' => false]) !!}
                    </div>
                </a>
            @endif

            {{-- Top News Stories stack --}}
            @include(Theme::getThemeNamespace('partials.home.top-news-inline'))
        </section>

        {{-- Right: Blindspot sidebar --}}
        <aside class="col-xl-3 col-lg-12 col-12 grimba-blindspot-rail">
            <header class="grimba-blindspot-rail__head">
                <span class="grimba-blindspot-rail__kicker">
                    <span class="blindspot-badge blindspot-badge--ghost">Angles morts</span>
                </span>
                <p class="grimba-blindspot-rail__desc">
                    Histoires couvertes de manière disproportionnée par un seul côté du spectre politique.
                    <a href="{{ url('/angles-morts') }}">En savoir plus</a>
                </p>
            </header>

            @foreach($blindspots as $b)
                @php
                    $blindTitle = GnTr::title($b);
                    $blindTranslated = GnTr::isTranslated($b);
                @endphp
                <a href="{{ $b->url }}" class="grimba-blind-card">
                    <div class="grimba-blind-card__media">
                        {!! Theme::partial('post-hero-img', ['post' => $b, 'size' => 'medium']) !!}
                    </div>
                    <div class="grimba-blind-card__body">
                        <span class="grimba-blind-card__tag">
                            <span class="blindspot-badge blindspot-badge--on-dark">Angle mort</span>
                        </span>
                        <h3 class="grimba-blind-card__title">{{ $blindTitle }}</h3>
                        @if($blindTranslated)
                            {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
                        @endif
                        {!! Theme::partial('home.coverage-bar', ['post' => $b, 'compact' => true, 'onDark' => true]) !!}
                    </div>
                </a>
            @endforeach

            <a href="{{ url('/angles-morts') }}" class="grimba-blindspot-rail__more">
                Voir le fil des angles morts →
            </a>

            {!! Theme::partial('bias-mix', ['variant' => 'compact']) !!}
        </aside>

    </div>
</section>
