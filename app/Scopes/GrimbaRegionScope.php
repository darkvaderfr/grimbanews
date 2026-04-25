<?php

namespace App\Scopes;

use Botble\Blog\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/*
 * S147 — region scope.
 *
 * Vader's region picker (S146) sets a `grimba_region` cookie but
 * nothing in the content pipeline read it — switching from "France"
 * to "UK" had no effect on the actual feed. This scope plumbs the
 * cookie into every Post query on the reader side: when the visitor
 * picks a specific region, only posts from sources whose country
 * matches that region's ISO list survive.
 *
 * Region → ISO-2 country code map:
 *   france        → FR
 *   uk            → GB (also accepts UK)
 *   us            → US
 *   canada        → CA
 *   africa        → 54 African country codes
 *   international → no filter (all posts)
 *
 * Posts whose source has no country (or no source_id at all) are
 * EXCLUDED when a specific region is active. They reappear as soon
 * as the visitor switches back to International. This keeps the
 * regional feed editorial-clean — the auto-created NewsAPI sources
 * with bias=unknown that Vader will classify via the S133 triage
 * queue inherit a country at that step.
 *
 * Admin requests are bypassed unconditionally so the editor sees
 * the full corpus regardless of which region cookie they happen to
 * have on their browser.
 */
class GrimbaRegionScope implements Scope
{
    private const COOKIE_NAME = 'grimba_region';

    /** ISO-2 codes per region. NULL = no filter. */
    private const REGION_MAP = [
        'france'  => ['FR'],
        'uk'      => ['GB', 'UK'],
        'us'      => ['US'],
        'canada'  => ['CA'],
        'africa'  => [
            'DZ', 'AO', 'BJ', 'BW', 'BF', 'BI', 'CV', 'CM', 'CF', 'TD',
            'KM', 'CG', 'CD', 'DJ', 'EG', 'GQ', 'ER', 'SZ', 'ET', 'GA',
            'GM', 'GH', 'GN', 'GW', 'CI', 'KE', 'LS', 'LR', 'LY', 'MG',
            'MW', 'ML', 'MR', 'MU', 'MA', 'MZ', 'NA', 'NE', 'NG', 'RW',
            'ST', 'SN', 'SC', 'SL', 'SO', 'ZA', 'SS', 'SD', 'TZ', 'TG',
            'TN', 'UG', 'ZM', 'ZW',
        ],
        'international' => null,
    ];

    public function apply(Builder $builder, Model $model): void
    {
        $request = request();
        if (! $request) {
            return;
        }

        // Skip on admin / dashboard surfaces. Editors need to see all
        // posts regardless of the cookie set in their personal browser.
        if ($this->isAdminContext($request)) {
            return;
        }

        $countries = $this->resolveCountries($request);
        if ($countries === null) {
            return; // International — no filter.
        }

        // Filter posts via their news_sources.country join. Use
        // whereIn on a sub-select so we don't disturb the model's
        // existing eager loads.
        $builder->whereIn(
            $model->getTable() . '.source_id',
            function ($q) use ($countries): void {
                $q->select('id')
                    ->from('news_sources')
                    ->whereIn('country', $countries);
            }
        );
    }

    private function isAdminContext($request): bool
    {
        // Botble's admin prefix can be customised; we check a few
        // common variants instead of hard-coding /admin.
        $path = (string) $request->path();
        if ($path === 'admin' || str_starts_with($path, 'admin/')) {
            return true;
        }
        // Botble routes API under /api/v1
        if (str_starts_with($path, 'api/')) {
            return true;
        }
        // NOT using app()->runningInConsole() here — PHP's built-in
        // dev server (php -S, used in local dev) reports CLI SAPI
        // and would always trigger the admin bypass. The earlier
        // `! $request` guard already covers true console runs
        // (artisan commands, queue workers): they never have a
        // bound request.
        return false;
    }

    private function resolveCountries($request): ?array
    {
        $region = (string) $request->cookie(self::COOKIE_NAME, 'international');

        // Migrate legacy values (matches the picker's own migration)
        $migrate = ['monde' => 'international', 'europe' => 'international', 'afrique' => 'africa'];
        $region = $migrate[$region] ?? $region;

        if (! array_key_exists($region, self::REGION_MAP)) {
            return null; // unknown — treat as international
        }
        return self::REGION_MAP[$region];
    }
}
