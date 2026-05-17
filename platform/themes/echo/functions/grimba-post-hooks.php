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
        // Copy admin Post form values posted under the grimba_* prefix
        // onto the model (our columns aren't in Post::$fillable, so
        // mass-assignment would otherwise drop them).
        $req = request();

        if ($req) {
            foreach (['source_id', 'story_cluster_id', 'bias_rating', 'is_blindspot'] as $key) {
                $formKey = 'grimba_' . $key;
                if ($req->has($formKey)) {
                    $raw = $req->input($formKey);
                    if ($key === 'is_blindspot') {
                        $post->{$key} = in_array($raw, ['1', 1, 'on', true, 'true'], true);
                    } elseif ($key === 'source_id' || $key === 'story_cluster_id') {
                        $post->{$key} = $raw === '' || $raw === null ? null : (int) $raw;
                    } else {
                        $post->{$key} = $raw === '' || $raw === null ? null : $raw;
                    }
                }
            }
        }

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
            'original_language' => $source->language,
        ];

        foreach ($copy as $field => $value) {
            $current = $post->{$field} ?? null;
            if ($current === null || $current === '' || $current === 'unknown') {
                $post->{$field} = $value;
            }
        }

        // Vader 2026-05-16 S-LANG-03 — universal language detection. If
        // source-copy left original_language empty (e.g. auto-created
        // news_sources row from the LiveNews / NewsAPI / newsdata.io
        // fetchers, where language=NULL), fall through to the
        // detector. It uses TLD + text n-gram signals — no DB / HTTP.
        $currentLang = $post->original_language ?? null;
        if ($currentLang === null || $currentLang === '' || $currentLang === 'unknown') {
            $detected = \App\Services\GrimbaLanguageDetector::detect([
                'source_language' => $source->language ?? null,
                'source_url'      => $source->website ?? null,
                'text_sample'     => trim(((string) ($post->name ?? '')) . "\n" . strip_tags((string) ($post->description ?? ''))),
            ]);

            if ($detected !== null) {
                $post->original_language = $detected;

                // Bubble verdict up to news_sources.language when that
                // row is currently NULL so subsequent posts skip the
                // detector entirely.
                if (empty($source->language)) {
                    DB::table('news_sources')->where('id', $sourceId)
                        ->whereNull('language')
                        ->update(['language' => $detected]);
                }
            }
        }
    });
});
