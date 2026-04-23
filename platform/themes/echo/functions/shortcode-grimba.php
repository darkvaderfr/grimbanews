<?php

use Botble\Blog\Models\Post;
use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Theme\Facades\Theme;
use Illuminate\Routing\Events\RouteMatched;

app('events')->listen(RouteMatched::class, function (): void {
    /*
     * [grimba-bias-legend]
     * Static explainer — renders the same partial used on /blog.
     */
    Shortcode::register(
        'grimba-bias-legend',
        __('GrimbaNews — Bias Legend'),
        __('Explainer + L/C/R/blindspot legend'),
        function (ShortcodeCompiler $shortcode): ?string {
            return Theme::partial('bias-legend');
        }
    );

    /*
     * [grimba-feed-balance limit="20"]
     * Live balance meter over the most recent $limit published posts.
     */
    Shortcode::register(
        'grimba-feed-balance',
        __('GrimbaNews — Feed Balance'),
        __('L/C/R share of the most recent published posts'),
        function (ShortcodeCompiler $shortcode): ?string {
            $limit = max(1, (int) ($shortcode->limit ?? 20));

            $posts = Post::query()
                ->where('status', 'published')
                ->latest()
                ->limit($limit)
                ->get();

            return Theme::partial('feed-balance', ['posts' => $posts]);
        }
    );

    /*
     * [grimba-comparison cluster="1"]
     * Embedded side-by-side for a given story cluster.
     */
    Shortcode::register(
        'grimba-comparison',
        __('GrimbaNews — Story Comparison'),
        __('Side-by-side comparison of a story cluster'),
        function (ShortcodeCompiler $shortcode): ?string {
            $clusterId = (int) ($shortcode->cluster ?? 0);

            if (! $clusterId) {
                return null;
            }

            $posts = Post::query()
                ->where('story_cluster_id', $clusterId)
                ->where('status', 'published')
                ->orderByRaw("CASE bias_rating WHEN 'left' THEN 1 WHEN 'center' THEN 2 WHEN 'right' THEN 3 ELSE 4 END")
                ->get();

            if ($posts->isEmpty()) {
                return null;
            }

            return Theme::partial('story-comparison', [
                'posts'      => $posts,
                'storyTitle' => $posts->first()->name,
            ]);
        }
    );
});
