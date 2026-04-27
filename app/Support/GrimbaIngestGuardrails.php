<?php

namespace App\Support;

use Botble\Blog\Models\Post;
use Illuminate\Database\Eloquent\Builder;

class GrimbaIngestGuardrails
{
    /**
     * @return array<int, string>
     */
    public static function flags(object $post): array
    {
        $flags = [];
        $bias = (string) ($post->bias_rating ?? 'unknown');
        $excerpt = trim(strip_tags((string) ($post->description ?? '')));
        $originalLanguage = strtolower(substr((string) ($post->original_language ?? ''), 0, 2));

        if (! ($post->source_id ?? null) || ! trim((string) ($post->source_name ?? ''))) {
            $flags[] = 'source manquante';
        }

        if (! in_array($bias, ['left', 'center', 'right'], true)) {
            $flags[] = 'biais inconnu';
        }

        if ($originalLanguage !== '' && $originalLanguage !== 'fr' && ! trim((string) ($post->translated_name ?? ''))) {
            $flags[] = 'traduction manquante';
        }

        if (mb_strlen($excerpt) < 80) {
            $flags[] = 'extrait trop court';
        }

        return $flags;
    }

    /**
     * @param callable(Builder): Builder|void|null $scope
     * @return array{published:int, blocked:int, reasons:array<int, string>}
     */
    public static function publishDrafts(array $ids, ?callable $scope = null): array
    {
        $published = 0;
        $blocked = 0;
        $reasons = [];

        foreach ($ids as $id) {
            $query = Post::query()
                ->where('id', (int) $id)
                ->where('status', 'draft');

            if ($scope) {
                $scoped = $scope($query);
                if ($scoped instanceof Builder) {
                    $query = $scoped;
                }
            }

            $post = $query->first();
            if (! $post) {
                continue;
            }

            $flags = self::flags($post);
            if ($flags !== []) {
                $blocked++;
                $reasons = array_merge($reasons, $flags);
                continue;
            }

            $post->status = 'published';
            $post->save();
            $published++;
        }

        return [
            'published' => $published,
            'blocked' => $blocked,
            'reasons' => array_values(array_unique($reasons)),
        ];
    }
}
