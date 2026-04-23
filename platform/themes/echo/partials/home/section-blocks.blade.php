@php
    use Botble\Blog\Models\Category;
    use Botble\Blog\Models\Post;

    $featuredCategories = Category::query()
        ->where('status', 'published')
        ->withCount(['posts' => fn ($q) => $q->where('posts.status', 'published')])
        ->orderByDesc('posts_count')
        ->limit(2)
        ->get();
@endphp

@foreach($featuredCategories as $cat)
    @php
        $latest = Post::query()
            ->whereHas('categories', fn ($q) => $q->where('categories.id', $cat->id))
            ->where('status', 'published')
            ->latest()
            ->first();

        $categoryBlindspots = Post::query()
            ->whereHas('categories', fn ($q) => $q->where('categories.id', $cat->id))
            ->where('status', 'published')
            ->where('is_blindspot', true)
            ->latest()
            ->limit(2)
            ->get();

        if ($categoryBlindspots->count() < 2) {
            $filler = Post::query()
                ->where('status', 'published')
                ->where('is_blindspot', true)
                ->whereNotIn('id', $categoryBlindspots->pluck('id'))
                ->latest()
                ->limit(2 - $categoryBlindspots->count())
                ->get();
            $categoryBlindspots = $categoryBlindspots->concat($filler);
        }
    @endphp

    <section class="grimba-section mt-5">
        <header class="grimba-section__head d-flex justify-content-between align-items-center mb-3">
            <h2 class="grimba-section__title">{{ $cat->name }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ $cat->url }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">Suivre</a>
                <a href="{{ $cat->url }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">Voir tout</a>
            </div>
        </header>

        <div class="row g-4">
            <div class="col-lg-7 col-12">
                @if($latest)
                    <a href="{{ $latest->url }}" class="grimba-section__hero">
                        @if($latest->image)
                            {{ RvMedia::image($latest->image, $latest->name, 'extra-large') }}
                        @endif
                        <div class="grimba-section__hero-body">
                            <span class="grimba-section__kicker">Dernières {{ strtolower($cat->name) }}</span>
                            <h3 class="grimba-section__hero-title">{{ $latest->name }}</h3>
                            {!! Theme::partial('home.coverage-bar', ['post' => $latest, 'compact' => false]) !!}
                        </div>
                    </a>
                @endif
            </div>

            <div class="col-lg-5 col-12 d-flex flex-column gap-3">
                <span class="grimba-section__kicker grimba-section__kicker--rail">Angles morts · {{ $cat->name }}</span>

                @foreach($categoryBlindspots as $b)
                    <a href="{{ $b->url }}" class="grimba-blind-card grimba-blind-card--wide">
                        @if($b->image)
                            <div class="grimba-blind-card__media">
                                {{ RvMedia::image($b->image, $b->name, 'medium') }}
                            </div>
                        @endif
                        <div class="grimba-blind-card__body">
                            <span class="blindspot-badge blindspot-badge--on-dark">Angle mort</span>
                            <h4 class="grimba-blind-card__title">{{ $b->name }}</h4>
                            {!! Theme::partial('home.coverage-bar', ['post' => $b, 'compact' => true, 'onDark' => true]) !!}
                        </div>
                    </a>
                @endforeach

                <form class="grimba-blind-subscribe" method="POST" action="{{ route('public.newsletter.subscribe') }}">
                    @csrf
                    <input type="hidden" name="source_key" value="section_blindspot_{{ \Illuminate\Support\Str::slug($cat->name) }}">
                    <span class="blindspot-badge blindspot-badge--on-dark">Newsletter angles morts</span>
                    <p class="small mb-2">Recevez chaque semaine les histoires ignorées par votre camp.</p>
                    <div class="d-flex gap-2">
                        <input type="email" name="email" required placeholder="Adresse e-mail" aria-label="Adresse e-mail">
                        <button type="submit" class="btn-grimba btn-grimba--solid btn-grimba--sm">S'inscrire</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endforeach
