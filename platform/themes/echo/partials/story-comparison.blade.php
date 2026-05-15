@php
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post as BlogPost;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Str;

    /**
     * Story Comparison Partial — side-by-side source coverage.
     *
     * @var \Illuminate\Support\Collection $posts  Posts in the cluster (ordered L / C / R)
     * @var string|null $storyTitle
     */

    GnTr::warm($posts);
    $storyTitle = $storyTitle ?? ($posts->first()->name ?? 'Comparaison');
    $__postIds = $posts->pluck('id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
    $__articleUrls = $__postIds->isEmpty()
        ? collect()
        : DB::table('slugs')
            ->whereIn('reference_id', $__postIds->all())
            ->where('reference_type', BlogPost::class)
            ->whereIn('prefix', ['article', 'blog'])
            ->orderByRaw("CASE prefix WHEN 'article' THEN 0 ELSE 1 END")
            ->get(['reference_id', 'key'])
            ->unique('reference_id')
            ->mapWithKeys(fn ($slug) => [(int) $slug->reference_id => url('/article/' . $slug->key)]);
@endphp

{!! Theme::partial('story-breakdown', ['posts' => $posts]) !!}
{{-- S323: dropped redundant source-diversity-meter — the breakdown's
      stacked bar + lane cards already surface the same L/C/R distribution. --}}

<div class="story-comparison">
    <div class="row g-3">
        @foreach($posts as $clusterPost)
            @php
                $__clusterTitle = GnTr::title($clusterPost) ?: $clusterPost->name;
                $__articleUrl = $__articleUrls->get((int) $clusterPost->id)
                    ?: ($clusterPost->url ?: url('/article/' . Str::slug($__clusterTitle ?: ('article-' . $clusterPost->id))));
            @endphp
            <div class="col-md-4 col-12">
                <article class="comparison-card glass-card h-100 p-3 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-uppercase opacity-75">
                            {{ $clusterPost->source_name ?? '—' }}
                        </span>
                        {!! Theme::partial('bias-badge', [
                            'bias'      => $clusterPost->bias_rating ?? null,
                            'showLabel' => true,
                            'size'      => 'sm',
                        ]) !!}
                    </div>

                    {{-- S329 — pre-resolve to skip RvMedia's 1920×1080 fallback. --}}
                    @php
                        $__cpImg = $clusterPost->image
                            ? \Botble\Media\Facades\RvMedia::getImageUrl($clusterPost->image, 'medium')
                            : null;
                        $__cpDefault = \Botble\Media\Facades\RvMedia::getDefaultImage(false, 'medium');
                        $__cpUsable = $__cpImg && $__cpImg !== $__cpDefault;
                    @endphp
                    @if($__cpUsable)
                        <a href="{{ $__articleUrl }}" class="d-block mb-3">
                            <img src="{{ $__cpImg }}" alt="{{ $__clusterTitle }}"
                                 loading="lazy" decoding="async"
                                 data-grimba-post-id="{{ $clusterPost->id }}"
                                 style="display:block; width:100%; height:auto; border-radius:8px;">
                        </a>
                    @endif

                    <h3 class="h5 mb-2">
                        <a href="{{ $__articleUrl }}" class="text-decoration-none title-hover">
                            {{ $__clusterTitle }}
                        </a>
                    </h3>

                    @if($__clusterDescription = GnTr::description($clusterPost))
                        <p class="small opacity-85 mb-3">
                            {!! BaseHelper::clean(Str::limit(strip_tags($__clusterDescription), 180)) !!}
                        </p>
                    @endif

                    <a href="{{ $__articleUrl }}" class="grimba-comparison-card__read mt-auto">
                        {{ __('Lire dans GrimbaNews') }}
                    </a>
                </article>
            </div>
        @endforeach
    </div>
</div>
