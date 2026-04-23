<?php

namespace Database\Seeders\Themes\Main;

use Botble\Ads\Models\Ads;
use Botble\Theme\Database\Seeders\ThemeSeeder;
use Illuminate\Support\Arr;

class AdsSeeder extends ThemeSeeder
{
    public function run(): void
    {
        Ads::query()->truncate();

        $now = $this->now();

        foreach ($this->getData() as $index => $item) {
            $index++;

            Ads::query()->create([
                ...Arr::except($item, 'metadata'),
                'name' => "Ads $index",
                'expired_at' => $now->clone()->addYears(5)->toDateString(),
                'location' => 'not_set',
                'order' => $index,
            ]);

        }
    }

    protected function getData(): array
    {
        return [];
    }
}
