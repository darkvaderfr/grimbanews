@php
    /**
     * S141 — sticky "voir les autres couvertures" chip on single-post.
     * Renders only when the post belongs to a story_cluster with ≥2
     * articles. Linked to /comparatif/{cluster_id}.
     *
     * Surfaces the unique GrimbaNews value prop the moment the
     * reader is consuming a single perspective: "we have 8 other
     * angles on this story".
     *
     * @var \Botble\Blog\Models\Post $post
     */

    use Botble\Blog\Models\Post;

    if (! $post->story_cluster_id) {
        return;
    }

    $cluster = Post::query()
        ->where('story_cluster_id', $post->story_cluster_id)
        ->where('status', 'published')
        ->where('id', '!=', $post->id)
        ->get(['bias_rating']);

    if ($cluster->count() < 1) {
        return;
    }

    $counts = ['left' => 0, 'center' => 0, 'right' => 0];
    foreach ($cluster as $cp) {
        $r = $cp->bias_rating ?? 'unknown';
        if (isset($counts[$r])) $counts[$r]++;
    }
    $other = $cluster->count();
@endphp

<a href="{{ url('/comparatif/' . $post->story_cluster_id) }}"
   class="grimba-comparatif-chip"
   aria-label="Voir {{ $other }} autres couvertures de la même histoire"
   style="
       position: sticky;
       bottom: 24px;
       z-index: 50;
       display: inline-flex;
       align-items: center;
       gap: 12px;
       margin: 32px auto 0;
       padding: 12px 18px;
       border-radius: 9999px;
       background: var(--gn-ink, #1a1713);
       color: var(--gn-paper, #f6f1e8);
       font-family: 'Public Sans', system-ui, sans-serif;
       font-weight: 600;
       font-size: 14px;
       letter-spacing: 0.3px;
       box-shadow: 0 12px 28px rgba(0,0,0,0.18);
       text-decoration: none;
       width: fit-content;
       max-width: 100%;
   ">
    {{-- Bias dots: visual signal of which sides exist in the cluster --}}
    <span style="display:inline-flex; gap:4px;" aria-hidden="true">
        @if($counts['left'] > 0)
            <span style="width:9px;height:9px;border-radius:50%;background:#3b82f6;" title="Gauche"></span>
        @endif
        @if($counts['center'] > 0)
            <span style="width:9px;height:9px;border-radius:50%;background:#a8a8a8;" title="Centre"></span>
        @endif
        @if($counts['right'] > 0)
            <span style="width:9px;height:9px;border-radius:50%;background:#e84c3d;" title="Droite"></span>
        @endif
    </span>
    <span>
        Voir {{ $other }} {{ $other === 1 ? 'autre couverture' : 'autres couvertures' }}
        de la même histoire →
    </span>
</a>
