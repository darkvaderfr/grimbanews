@php
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post;
    use Illuminate\Support\Str;

    $biasMeta = [
        'left' => [
            'label' => __('Gauche'),
            'description' => __('Les articles les plus lus côté gauche.'),
        ],
        'center' => [
            'label' => __('Centre'),
            'description' => __('Ce qui attire le plus les lecteurs du centre.'),
        ],
        'right' => [
            'label' => __('Droite'),
            'description' => __('Les titres qui performent côté droit.'),
        ],
    ];

    $topByBias = collect(array_keys($biasMeta))
        ->mapWithKeys(fn (string $bias) => [
            $bias => Post::query()
                ->where('status', 'published')
                ->where('bias_rating', $bias)
                ->where('views', '>', 0)
                ->with('categories')
                ->orderByDesc('views')
                ->latest()
                ->limit(4)
                ->get(),
        ]);

    $hasStories = $topByBias->flatten(1)->isNotEmpty();
@endphp

@if($hasStories)
    <section class="grimba-most-read mt-5" aria-labelledby="grimba-most-read-title">
        <header class="grimba-most-read__head">
            <div>
                <span class="grimba-most-read__eyebrow">{{ __('Lecture publique') }}</span>
                <h2 id="grimba-most-read-title" class="grimba-most-read__title">{{ __('Les plus lus par tendance') }}</h2>
            </div>
            <p class="grimba-most-read__dek">
                {{ __('Un instantané GroundNews-style de ce qui monte à gauche, au centre et à droite.') }}
            </p>
        </header>

        <div class="grimba-most-read__grid">
            @foreach($biasMeta as $bias => $meta)
                @php $items = $topByBias[$bias] ?? collect(); @endphp
                <article class="grimba-most-read__panel grimba-most-read__panel--{{ $bias }}">
                    <div class="grimba-most-read__panel-head">
                        <span class="grimba-most-read__dot grimba-most-read__dot--{{ $bias }}"></span>
                        <div>
                            <h3>{{ $meta['label'] }}</h3>
                            <p>{{ $meta['description'] }}</p>
                        </div>
                    </div>

                    @if($items->isNotEmpty())
                        <ol class="grimba-most-read__list">
                            @foreach($items as $index => $post)
                                @php
                                    $title = GnTr::title($post);
                                    $isTranslated = GnTr::isTranslated($post);
                                    $source = $post->source_name ?: optional($post->categories->first())->name;
                                @endphp
                                <li class="grimba-most-read__item">
                                    <span class="grimba-most-read__rank">{{ $index + 1 }}</span>
                                    <div class="grimba-most-read__body">
                                        <a href="{{ $post->url }}" class="grimba-most-read__headline">{{ $title }}</a>
                                        <div class="grimba-most-read__meta">
                                            @if($source)
                                                <span>{{ Str::limit($source, 32) }}</span>
                                            @endif
                                            <span>{{ trans_choice(':count vue|:count vues', (int) $post->views, ['count' => number_format((int) $post->views)]) }}</span>
                                        </div>
                                        @if($isTranslated)
                                            {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="grimba-most-read__empty">
                            {{ __('Pas encore assez de lectures classées :bias.', ['bias' => mb_strtolower($meta['label'])]) }}
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endif
