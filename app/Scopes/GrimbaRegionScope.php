<?php

namespace App\Scopes;

use App\Ground\Regions;
use Botble\Blog\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

/*
 * S147 / Fleet K2 — edition scope (4-region rework, 2026-05-05).
 *
 * Vader directive: simplify the editorial cuts to four regions.
 *   africa       → posts whose source country is in Regions::AFRICA
 *   europe       → posts whose source country is in Regions::EUROPE
 *   americas     → posts whose source country is in Regions::AMERICAS
 *   international → posts whose source country is NOT in any of the
 *                   three named regions (i.e., Asia, Oceania, Middle
 *                   East, Pacific, Antarctica) PLUS sources without a
 *                   country tag — the "rest of the world" surface.
 *
 * Country lists + label / migration helpers live in App\Ground\Regions.
 *
 * Admin requests are bypassed unconditionally so the editor sees the
 * full corpus regardless of which cookie they happen to have set.
 */
class GrimbaRegionScope implements Scope
{
    public const COOKIE_NAME = 'grimba_region';

    public function apply(Builder $builder, Model $model): void
    {
        $request = request();
        if (! $request) {
            return;
        }

        if ($this->isAdminContext($request)) {
            return;
        }

        $region = $this->resolveRegion($request);
        if ($region === null) {
            return;
        }

        // International (default editorial view) — NO filter. Per Vader
        // 2026-05-16: "the international shows all articles across
        // regions". Named regions below are the focused cuts.
        if ($region === 'international') {
            return;
        }

        $table = $model->getTable();

        // Preferred path: filter by the first-class posts.editorial_region
        // column set at ingest. Cheaper than the join-through-source
        // pattern (no subquery), reflects the editorial intent of the
        // post directly, and lets editors override the region on a
        // case-by-case basis without changing source records.
        if (Schema::hasColumn($table, 'editorial_region')) {
            $builder->where($table . '.editorial_region', $region);

            return;
        }

        // Legacy fallback for environments where the migration hasn't
        // run yet — derive region from the joined source country list.
        $countries = Regions::countries($region);
        if ($countries === null) {
            return;
        }

        $builder->whereIn(
            $table . '.source_id',
            function ($q) use ($countries): void {
                $q->select('id')
                    ->from('news_sources')
                    ->whereIn('country', $countries);
            }
        );
    }

    private function isAdminContext($request): bool
    {
        $path = (string) $request->path();
        if ($path === 'admin' || str_starts_with($path, 'admin/')) {
            return true;
        }
        if (str_starts_with($path, 'api/')) {
            return true;
        }
        return false;
    }

    private function resolveRegion($request): ?string
    {
        if (! $request->hasCookie(self::COOKIE_NAME)) {
            return null;
        }

        $raw = (string) $request->cookie(self::COOKIE_NAME, 'international');
        return Regions::migrate($raw);
    }
}
