<?php

namespace Database\Seeders\Themes\Main;

use ArchiElite\Announcement\Models\Announcement;
use Botble\Base\Supports\BaseSeeder;
use Botble\Setting\Facades\Setting;
use Carbon\Carbon;

class AnnouncementSeeder extends BaseSeeder
{
    public function run(): void
    {
        Announcement::query()->truncate();

        $announcements = [
            'Cyber Monday: Save big on the Creative Cloud All Apps plan for individuals through 2 Dec',
            'Students and teachers save a massive 71% on Creative Cloud All Apps',
            'Black Friday and Cyber Monday 2023 Deals for Motion Designers, grab it now!',
        ];

        $now = Carbon::now();

        foreach ($announcements as $key => $value) {
            Announcement::query()->create([
                'name' => sprintf('Announcement %s', $key + 1),
                'content' => $value,
                'start_date' => $now,
                'dismissible' => true,
            ]);
        }

        $settings = $this->getSettingData();

        Setting::delete(array_keys($settings));

        Setting::set($settings)->save();
    }

    public function getSettingData(): array
    {
        return [
            'announcement_max_width' => '1390',
            'announcement_text_color' => '#fefefe',
            'announcement_background_color' => theme_option('primary_color', '#537fe7'),
            'announcement_text_alignment' => 'center',
            'announcement_dismissible' => '1',
            'announcement_font_size' => 14,
            'announcement_font_size_unit' => 'px',
        ];
    }
}
