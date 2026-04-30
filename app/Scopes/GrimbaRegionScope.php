<?php

namespace App\Scopes;

use Botble\Blog\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/*
 * S147 — edition scope.
 *
 * The reader-facing edition toggle sets a `grimba_region` cookie.
 * Public Post queries read it here. Africa filters to sources whose
 * country is on the continent; International is intentionally broad
 * and unfiltered.
 *
 * Edition → ISO-2 country code map:
 *   africa        → 54 African country codes
 *   international → no filter
 *
 * Posts whose source has no country (or no source_id at all) are
 * excluded in Africa mode and reappear in International mode.
 *
 * Admin requests are bypassed unconditionally so the editor sees
 * the full corpus regardless of which region cookie they happen to
 * have on their browser.
 */
class GrimbaRegionScope implements Scope
{
    private const COOKIE_NAME = 'grimba_region';

    /** ISO-2 codes per edition. NULL = no filter. */
    private const REGION_MAP = [
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

        // Migrate legacy picker values from the old six-region model.
        $migrate = [
            'monde' => 'international',
            'europe' => 'international',
            'afrique' => 'africa',
            'france' => 'international',
            'uk' => 'international',
            'us' => 'international',
            'canada' => 'international',
        ];
        $region = $migrate[$region] ?? $region;

        if (! array_key_exists($region, self::REGION_MAP)) {
            return null; // unknown — treat as international
        }
        return self::REGION_MAP[$region];
    }
}
