@php
    /**
     * S148 — Similar News Topics sidebar block. Surfaces other recent
     * story_clusters that share at least one category with the
     * current post. Mirrors the GroundNews sidebar.
     *
     * @var \Botble\Blog\Models\Post $post
     */

    use Botble\Blog\Models\Post;

    $catIds = $post->categories?->pluck('id')->all() ?? [];
    if (empty($catIds)) {
        $similar = collect();
    } else {
        $similar = Post::query()
            ->whereNotNull('story_cluster_id')
            ->where('story_cluster_id', '!=', $post->story_cluster_id)
            ->where('status', 'published')
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $catIds))
            ->select('story_cluster_id', \Illuminate\Support\Facades\DB::raw('MAX(name) as topic'))
            ->groupBy('story_cluster_id')
            ->latest('story_cluster_id')
            ->limit(6)
            ->get();
    }
@endphp

@if($similar->isNotEmpty())
    <aside class="grimba-story-similar glass-panel p-3 mb-3">
        <h2 class="h6 mb-2" style="font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; text-transform:uppercase; font-size:13px; opacity:0.75;">
            Sujets similaires
        </h2>
        <ul class="list-unstyled m-0">
            @foreach($similar as $row)
                <li style="padding:8px 0; border-bottom:1px dashed rgba(0,0,0,0.06); font-size:14px;">
                    <a href="{{ url('/comparatif/' . $row->story_cluster_id) }}"
                       style="color:var(--gn-ink,#1a1713); text-decoration:none; line-height:1.35; display:block;">
                        {{ \Illuminate\Support\Str::limit($row->topic, 80) }}
                        <span style="color:#c0392b; margin-left:4px;">→</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>
@endif
