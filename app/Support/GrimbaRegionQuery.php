<?php

namespace App\Support;

use App\Ground\Regions;

class GrimbaRegionQuery
{
    public static function selectedRegion(): ?string
    {
        if (! request()->hasCookie('grimba_region')) {
            return null;
        }

        return Regions::migrate((string) request()->cookie('grimba_region', 'international'));
    }

    public static function applyToSourceCountry($query, string $countryColumn): mixed
    {
        $region = self::selectedRegion();
        if ($region === null) {
            return $query;
        }

        if ($region === 'international') {
            $excluded = Regions::otherNamedCodes();

            return $query->where(function ($scoped) use ($countryColumn, $excluded): void {
                $scoped->whereNull($countryColumn)
                    ->orWhereNotIn($countryColumn, $excluded);
            });
        }

        $countries = Regions::countries($region);
        if ($countries === null) {
            return $query;
        }

        return $query->whereIn($countryColumn, $countries);
    }
}
