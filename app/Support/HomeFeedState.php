<?php

namespace App\Support;

use Botble\Blog\Models\Post;
use Illuminate\Support\Collection;

/**
 * Internal allocation state for {@see GrimbaHomeFeed}. Tracks which post
 * IDs have been claimed by earlier sections and how many times each
 * source has been picked so one publisher cannot dominate.
 *
 * @internal
 */
class HomeFeedState
{
    /** @var array<int, true> */
    public array $shown = [];

    /** @var array<int, int> */
    private array $sourceCount = [];

    public function __construct(private int $sourceCap)
    {
    }

    public function isShown(int $postId): bool
    {
        return isset($this->shown[$postId]);
    }

    public function canTakeSource(mixed $sourceId): bool
    {
        if ($sourceId === null) {
            return true;
        }

        $sid = (int) $sourceId;
        if ($sid <= 0) {
            return true;
        }

        return ($this->sourceCount[$sid] ?? 0) < $this->sourceCap;
    }

    public function take(Post $post): bool
    {
        $id = (int) $post->id;
        if (isset($this->shown[$id])) {
            return false;
        }

        if (! $this->canTakeSource($post->source_id ?? null)) {
            return false;
        }

        $this->shown[$id] = true;

        $sid = (int) ($post->source_id ?? 0);
        if ($sid > 0) {
            $this->sourceCount[$sid] = ($this->sourceCount[$sid] ?? 0) + 1;
        }

        return true;
    }

    public function firstAvailable(Collection $candidates): ?Post
    {
        foreach ($candidates as $post) {
            if (! $this->isShown((int) $post->id) && $this->canTakeSource($post->source_id ?? null)) {
                return $post;
            }
        }

        return null;
    }

    /**
     * @return array<int, int>
     */
    public function shownIds(): array
    {
        return array_keys($this->shown);
    }

    public function relaxSourceCap(int $newCap): void
    {
        if ($newCap > $this->sourceCap) {
            $this->sourceCap = $newCap;
        }
    }
}
