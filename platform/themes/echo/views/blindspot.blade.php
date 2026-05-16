@php
    Theme::layout('grimba-chrome');
    /**
     * Blindspot Feed — histoires couvertes par un seul camp.
     *
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $posts
     * @var int $focusClusterId
     * @var string $forFilter  Optional. 'all' | 'left' | 'right'.
     */
    $forFilter = $forFilter ?? 'all';
@endphp

{!! Theme::partial('breadcrumbs', ['title' => __('Angles morts')]) !!}

<section class="blindspot-page py-5">
    <div class="container">
        <header class="glass-panel grimba-editorial-ribbon p-4 mb-4">
            <span class="blindspot-badge mb-2">{{ __('Angle mort') }}</span>
            <h1 class="h2 mt-2 mb-2">{{ __("Les histoires qu'un seul camp couvre") }}</h1>
            <p class="blindspot-page__lede mb-3">
                {{ __("Un angle mort est une histoire importante rapportée presque exclusivement par un côté du spectre politique. GrimbaNews les signale pour que vous sachiez ce qu'on ne vous raconte pas.") }}
            </p>

            {{-- S315 — bias-side filter tabs (Ground-fidelity). --}}
            <div class="d-flex gap-2 flex-wrap" role="tablist" aria-label="{{ __('Filtrer les angles morts') }}">
                @php
                    $tabs = [
                        'all'   => ['label' => __('Tous'),               'color' => '#1a1713'],
                        'left'  => ['label' => __('Pour la gauche'),     'color' => '#3b82f6'],
                        'right' => ['label' => __('Pour la droite'),     'color' => '#e84c3d'],
                    ];
                @endphp
                @foreach($tabs as $key => $meta)
                    @php $active = $forFilter === $key; @endphp
                    <a href="{{ url('/angles-morts') . ($key === 'all' ? '' : '?for=' . $key) }}"
                       class="btn-grimba btn-grimba--sm {{ $active ? 'btn-grimba--solid' : 'btn-grimba--ghost' }}"
                       role="tab"
                       aria-selected="{{ $active ? 'true' : 'false' }}"
                       @if(! $active) style="border-color:{{ $meta['color'] }}55;color:{{ $meta['color'] }};" @endif>
                        @if($key !== 'all')
                            <span aria-hidden="true" style="display:inline-block;width:7px;height:7px;border-radius:50%;background:{{ $meta['color'] }};margin-right:6px;"></span>
                        @endif
                        {{ $meta['label'] }}
                    </a>
                @endforeach
            </div>
        </header>

        @if($posts->isEmpty())
            <div class="glass-panel p-4 text-center">
                <p class="mb-0">{{ __('Aucun angle mort identifié pour le moment.') }}</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($posts as $post)
                    @php $isFocus = ($focusClusterId ?? 0) > 0 && (int) $post->story_cluster_id === (int) $focusClusterId; @endphp
                    <div class="col-lg-4 col-md-6 col-12" id="cluster-{{ (int) $post->story_cluster_id }}">
                        <div @if($isFocus) class="glass-panel p-2" style="box-shadow:0 16px 50px rgba(192,57,43,0.18); border-color:rgba(192,57,43,0.26);" @endif>
                            @if($isFocus)
                                <div class="small fw-semibold text-uppercase mb-2" style="letter-spacing:0.08em; color:#c0392b;">
                                    {{ __('Histoire liée') }}
                                </div>
                            @endif
                            @include(Theme::getThemeNamespace('partials.blog.post.partials.items.card'), ['post' => $post])
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {!! $posts->links() !!}
            </div>
        @endif
    </div>
</section>
