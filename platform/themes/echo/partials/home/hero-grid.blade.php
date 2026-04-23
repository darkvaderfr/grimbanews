@php
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

    // Hero = most recent featured post that's part of a balanced cluster.
    // Fallbacks: latest featured / latest published.
    $hero = Post::query()
            ->where('status', 'published')
            ->where('is_featured', true)
            ->whereIn('story_cluster_id', $balancedClusters)
            ->latest()->first()
        ?? Post::query()->where('status', 'published')->where('is_featured', true)->latest()->first()
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
                    <li class="grimba-briefing__item">
                        @if($p->image)
                            <a href="{{ $p->url }}" class="grimba-briefing__thumb">
                                {{ RvMedia::image($p->image, $p->name, 'small') }}
                            </a>
                        @endif
                        <div class="grimba-briefing__body">
                            <a href="{{ $p->url }}" class="grimba-briefing__headline">{{ $p->name }}</a>
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
                <a href="{{ $hero->url }}" class="grimba-hero__media">
                    @if($hero->image)
                        {{ RvMedia::image($hero->image, $hero->name, 'extra-large') }}
                    @endif
                    <div class="grimba-hero__gradient"></div>
                    <div class="grimba-hero__text">
                        <h1 class="grimba-hero__title">{{ $hero->name }}</h1>
                        @if($hero->description)
                            <p class="grimba-hero__desc">{{ \Illuminate\Support\Str::limit(strip_tags($hero->description), 140) }}</p>
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
                <a href="{{ $b->url }}" class="grimba-blind-card">
                    @if($b->image)
                        <div class="grimba-blind-card__media">
                            {{ RvMedia::image($b->image, $b->name, 'medium') }}
                        </div>
                    @endif
                    <div class="grimba-blind-card__body">
                        <span class="grimba-blind-card__tag">
                            <span class="blindspot-badge blindspot-badge--on-dark">Angle mort</span>
                        </span>
                        <h3 class="grimba-blind-card__title">{{ $b->name }}</h3>
                        {!! Theme::partial('home.coverage-bar', ['post' => $b, 'compact' => true, 'onDark' => true]) !!}
                    </div>
                </a>
            @endforeach

            <a href="{{ url('/angles-morts') }}" class="grimba-blindspot-rail__more">
                Voir le fil des angles morts →
            </a>

            <section class="grimba-bias-profile">
                <h4 class="h6 mb-1">Votre biais de lecture</h4>
                <p class="small opacity-75 mb-2">0 source · 0 article</p>
                <div style="display:flex;height:8px;border-radius:9999px;overflow:hidden;background:rgba(0,0,0,.08);">
                    <div style="width:33%;background:#3b82f6;"></div>
                    <div style="width:34%;background:#cfa24a;"></div>
                    <div style="width:33%;background:#ef4444;"></div>
                </div>
                <a href="#demo" class="small text-decoration-underline mt-2 d-inline-block">Voir la démo</a>
            </section>
        </aside>

    </div>
</section>
