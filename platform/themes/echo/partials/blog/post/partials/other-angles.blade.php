@php
    /**
     * Sibling posts in the same story cluster — "Autres angles".
     *
     * @var \Botble\Blog\Models\Post $post
     */
    use Botble\Blog\Models\Post;

    $siblings = collect();
    if ($post->story_cluster_id) {
        $siblings = Post::query()
            ->where('story_cluster_id', $post->story_cluster_id)
            ->where('status', 'published')
            ->where('id', '!=', $post->id)
            ->orderByRaw("CASE bias_rating WHEN 'left' THEN 1 WHEN 'center' THEN 2 WHEN 'right' THEN 3 ELSE 4 END")
            ->get();
    }
@endphp

@if($siblings->isNotEmpty())
    <section class="grimba-other-angles">
        <header class="grimba-other-angles__head">
            <h2 class="grimba-other-angles__title">{{ __('Autres angles sur la même histoire') }}</h2>
            <a href="{{ url('/comparatif/' . $post->story_cluster_id) }}" class="grimba-other-angles__all">
                {{ __('Voir la comparaison complète') }} →
            </a>
        </header>

        <div class="row g-3">
            @foreach($siblings as $sib)
                <div class="col-md-{{ $siblings->count() === 1 ? '12' : ($siblings->count() === 2 ? '6' : '4') }} col-12">
                    <a href="{{ $sib->url }}" class="grimba-other-angles__card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="grimba-other-angles__source">{{ $sib->source_name ?? '—' }}</span>
                            {!! Theme::partial('bias-badge', [
                                'bias'      => $sib->bias_rating,
                                'showLabel' => true,
                                'size'      => 'sm',
                            ]) !!}
                        </div>
                        <h3 class="grimba-other-angles__headline">{{ $sib->name }}</h3>
                        @if($sib->description)
                            <p class="grimba-other-angles__desc">{{ \Illuminate\Support\Str::limit(strip_tags($sib->description), 120) }}</p>
                        @endif
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@endif
