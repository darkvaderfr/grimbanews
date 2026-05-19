@php
    /*
     * Wave MMMMMM (Vader 2026-05-19) — "Autres dossiers" rail at the
     * bottom of every article page. Drives session depth by surfacing
     * 3-4 OTHER story clusters covering the same primary topic. The
     * legacy Botble related-posts hook below uses random per-post
     * pairing — useful but not topic-relevant. This rail adds the
     * editorial layer: cross-dossier navigation by topic match.
     *
     * @var \Botble\Blog\Models\Post $post
     */

    use App\Support\GrimbaEditorialCategories as GnCats;
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post;
    use Illuminate\Support\Facades\DB;

    $post->loadMissing('categories');
    $__rdTopic = GnCats::primaryTopicFor($post);
    $__rdCards = collect();

    if ($__rdTopic !== null) {
        $__rdCurrentClusterId = $post->story_cluster_id ?? 0;

        // Find clusters covering the same primary topic, excluding the
        // current cluster. Order by recency. We pluck cluster_ids first
        // so we can fetch ONE representative post per cluster cheaply.
        $__rdClusterIds = DB::table('posts')
            ->join('post_categories', 'posts.id', '=', 'post_categories.post_id')
            ->where('post_categories.category_id', $__rdTopic->id)
            ->where('posts.status', 'published')
            ->whereNotNull('posts.story_cluster_id')
            ->where('posts.story_cluster_id', '!=', $__rdCurrentClusterId)
            ->orderByDesc('posts.created_at')
            ->limit(40)
            ->pluck('story_cluster_id')
            ->unique()
            ->take(4)
            ->all();

        if (! empty($__rdClusterIds)) {
            // Per-cluster representative post + per-cluster source count
            // for the "5 sources" footer chip.
            $__rdCounts = DB::table('posts')
                ->whereIn('story_cluster_id', $__rdClusterIds)
                ->where('status', 'published')
                ->groupBy('story_cluster_id')
                ->select('story_cluster_id', DB::raw('COUNT(*) as c'))
                ->pluck('c', 'story_cluster_id');

            $__rdCards = Post::query()
                ->whereIn('story_cluster_id', $__rdClusterIds)
                ->where('status', 'published')
                ->with('categories')
                ->tap(fn ($q) => GnTr::orderForTargetLocale($q, withRecency: false))
                ->get(['id', 'name', 'translated_name', 'translated_to', 'image', 'source_name', 'bias_rating', 'story_cluster_id', 'created_at'])
                ->groupBy('story_cluster_id')
                ->map(fn ($grp) => $grp->first())
                ->values();
        }
    }

    $__rdBiasMeta = [
        'left' => ['label' => __('Gauche'), 'color' => '#3b82f6'],
        'center' => ['label' => __('Centre'), 'color' => '#a8a8a8'],
        'right' => ['label' => __('Droite'), 'color' => '#e84c3d'],
    ];
@endphp

@if ($__rdCards->isNotEmpty())
    <section class="grimba-related-dossiers" aria-labelledby="grimba-related-dossiers-title" data-grimba-related-dossiers>
        <header class="grimba-related-dossiers__head">
            <span class="grimba-related-dossiers__kicker">{{ __('Autres dossiers') }}</span>
            <h2 id="grimba-related-dossiers-title" class="grimba-related-dossiers__title">
                {{ __('Plus dans :topic', ['topic' => $__rdTopic->name]) }}
            </h2>
            @if ($__rdTopic->slugable?->key ?? null)
                <a href="{{ url('/blog/' . $__rdTopic->slugable->key) }}" class="grimba-related-dossiers__more">
                    {{ __('Voir tout le sujet') }} →
                </a>
            @endif
        </header>
        <div class="grimba-related-dossiers__grid">
            @foreach ($__rdCards as $__rd)
                @php
                    $__rdTitle = GnTr::title($__rd);
                    $__rdBias = $__rd->bias_rating ?? 'unknown';
                    $__rdBiasColor = $__rdBiasMeta[$__rdBias]['color'] ?? '#6b6459';
                    $__rdBiasLabel = $__rdBiasMeta[$__rdBias]['label'] ?? __('Non classé');
                    $__rdCount = (int) ($__rdCounts[$__rd->story_cluster_id] ?? 1);
                    $__rdHref = url('/comparatif/' . $__rd->story_cluster_id);
                @endphp
                <a href="{{ $__rdHref }}" class="grimba-related-dossiers__card" aria-label="{{ __('Lire le dossier : :title', ['title' => $__rdTitle]) }}">
                    <div class="grimba-related-dossiers__media">
                        {!! Theme::partial('post-hero-img', ['post' => $__rd, 'size' => 'thumb-medium']) !!}
                        <span class="grimba-related-dossiers__bias-chip" style="--rd-bias: {{ $__rdBiasColor }};" title="{{ $__rdBiasLabel }}">
                            {{ $__rdBiasLabel }}
                        </span>
                    </div>
                    <div class="grimba-related-dossiers__body">
                        <h3 class="grimba-related-dossiers__card-title">{{ $__rdTitle }}</h3>
                        <span class="grimba-related-dossiers__card-meta">
                            {{ trans_choice(':count source contributrice|:count sources contributrices', $__rdCount, ['count' => $__rdCount]) }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
