@php
    Theme::layout('grimba-chrome');
    /**
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $posts
     * @var array<int> $followedIds
     */
    use Botble\Blog\Models\Category;

    $followed = Category::query()->whereIn('id', $followedIds)->get();
@endphp

<section class="grimba-foryou py-5">
    <div class="container">

        <header class="glass-panel p-4 p-md-5 mb-4">
            <span class="grimba-methodology__kicker">{{ __('Pour vous') }}</span>
            <h1 class="grimba-methodology__title mt-2 mb-2">
                @if($followed->isEmpty())
                    {{ __('Suivez des sujets pour construire votre fil') }}
                @else
                    {{ __('Votre fil') }} — {{ $followed->count() }} {{ $followed->count() === 1 ? __('sujet suivi') : __('sujets suivis') }}
                @endif
            </h1>
            <p class="grimba-foryou__lede mb-3">
                @if($followed->isEmpty())
                    {!! __('Cliquez sur le <strong>+</strong> à côté d\'un sujet pour l\'ajouter à votre fil.') !!}
                    {{ __('Votre sélection reste locale à votre navigateur — aucun compte requis.') }}
                @else
                    {{ __('Les histoires récentes de') }}
                    @foreach($followed as $i => $c)
                        <strong>{{ $c->name }}</strong>@if($i < $followed->count() - 1), @endif
                    @endforeach.
                    {{ __('Ajustez votre sélection depuis la barre de sujets ou en cliquant sur les chips ci-dessous.') }}
                @endif
            </p>
            @if($followed->isNotEmpty())
                <div class="d-flex gap-2 flex-wrap">
                    @foreach($followed as $c)
                        <button type="button"
                                class="btn-grimba btn-grimba--ghost btn-grimba--sm grimba-foryou__unfollow"
                                data-category-id="{{ $c->id }}">
                            <span>{{ $c->name }}</span>
                            <span aria-hidden="true">×</span>
                        </button>
                    @endforeach
                    <a href="{{ url('/') }}" class="btn-grimba btn-grimba--solid btn-grimba--sm">+ {{ __('Ajouter des sujets') }}</a>
                </div>
            @else
                <a href="{{ url('/') }}" class="btn-grimba btn-grimba--solid">{{ __('Choisir des sujets') }}</a>
            @endif
        </header>

        {{-- S100 — bias-mix widget. Reads grimba_read cookie, no server state. --}}
        <div class="glass-panel p-3 p-md-4 mb-4">
            {!! Theme::partial('bias-mix', ['variant' => 'full']) !!}
        </div>

        @if(($readHistoryCount ?? 0) > 10 && isset($avoidedTopics) && $avoidedTopics->isNotEmpty())
            <div class="glass-panel p-4 p-md-5 mb-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                    <div>
                        <span class="grimba-methodology__kicker">{{ __('Sujets que vous évitez') }}</span>
                        <h2 class="h1 mt-2 mb-2">{{ __('Vos angles morts personnels') }}</h2>
                        <p class="mb-0 opacity-85">
                            {{ __('D’après vos derniers articles lus, ces rubriques récentes n’apparaissent pas encore dans votre historique.') }}
                        </p>
                    </div>
                    <div class="text-lg-end opacity-75">
                        <strong>{{ $readHistoryCount }}</strong><br>
                        <span>{{ __('articles lus pris en compte') }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @foreach($avoidedTopics as $topic)
                        <a class="btn-grimba btn-grimba--ghost btn-grimba--sm" href="{{ url('/blog?categorie=' . $topic->id) }}">
                            {{ $topic->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($posts->isEmpty())
            <div class="glass-panel p-4 text-center">
                <p class="mb-0">{{ __("Aucune histoire correspondant à vos sujets pour l'instant.") }}</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($posts as $post)
                    <div class="col-lg-4 col-md-6 col-12">
                        @include(Theme::getThemeNamespace('partials.blog.post.partials.items.grid'), ['post' => $post])
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {!! $posts->links() !!}
            </div>
        @endif
    </div>
</section>

<script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        document.querySelectorAll('.grimba-foryou__unfollow').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.categoryId;
                await fetch(@json(route('public.topics.follow')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ category_id: id, action: 'unfollow' })
                });
                window.location.reload();
            });
        });
    })();
</script>
