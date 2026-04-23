<?php

/*
 * GrimbaNews — Post saving hook.
 *
 * When a post has a source_id pointing at news_sources, auto-fill any
 * unset bias/ownership/credibility/source_name fields from the source
 * row. Editors can still override explicitly — only empty values are
 * copied.
 */

use Botble\Blog\Models\Post;
use Illuminate\Support\Facades\DB;

app()->booted(function (): void {
    if (! class_exists(Post::class)) {
        return;
    }

    Post::saving(function (Post $post): void {
        $sourceId = $post->source_id ?? null;

        if (! $sourceId) {
            return;
        }

        $source = DB::table('news_sources')->where('id', $sourceId)->first();

        if (! $source) {
            return;
        }

        $copy = [
            'source_name'       => $source->name,
            'bias_rating'       => $source->bias_rating,
            'ownership_type'    => $source->ownership_type,
            'credibility_score' => $source->credibility_score,
        ];

        foreach ($copy as $field => $value) {
            $current = $post->{$field} ?? null;
            if ($current === null || $current === '' || $current === 'unknown') {
                $post->{$field} = $value;
            }
        }
    });
});
