@php
    Theme::layout('grimba-chrome');
    /**
     * Blindspot Feed — histoires couvertes par un seul camp.
     *
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $posts
     * @var int $focusClusterId
     */
@endphp

{!! Theme::partial('breadcrumbs', ['title' => __('Angles morts')]) !!}

<section class="blindspot-page py-5">
    <div class="container">
        <header class="glass-panel p-4 mb-4">
            <span class="blindspot-badge mb-2">{{ __('Angle mort') }}</span>
            <h1 class="h2 mt-2 mb-2">{{ __("Les histoires qu'un seul camp couvre") }}</h1>
            <p class="mb-0 opacity-85">
                {{ __("Un angle mort est une histoire importante rapportée presque exclusivement par un côté du spectre politique. GrimbaNews les signale pour que vous sachiez ce qu'on ne vous raconte pas.") }}
            </p>
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
