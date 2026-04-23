@php
    /**
     * Blindspot Feed — histoires couvertes par un seul camp.
     *
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $posts
     */
@endphp

{!! Theme::partial('breadcrumbs', ['title' => 'Angles morts']) !!}

<section class="blindspot-page py-5">
    <div class="container">
        <header class="glass-panel p-4 mb-4">
            <span class="blindspot-badge mb-2">Angle mort</span>
            <h1 class="h2 mt-2 mb-2">Les histoires qu'un seul camp couvre</h1>
            <p class="mb-0 opacity-85">
                Un angle mort est une histoire importante rapportée presque exclusivement par un
                côté du spectre politique. GrimbaNews les signale pour que vous sachiez ce qu'on
                ne vous raconte pas.
            </p>
        </header>

        @if($posts->isEmpty())
            <div class="glass-panel p-4 text-center">
                <p class="mb-0">Aucun angle mort identifié pour le moment.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($posts as $post)
                    <div class="col-lg-4 col-md-6 col-12">
                        @include(Theme::getThemeNamespace('partials.blog.post.partials.items.card'), ['post' => $post])
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {!! $posts->links() !!}
            </div>
        @endif
    </div>
</section>
