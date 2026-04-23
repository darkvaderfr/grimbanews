<?php

namespace Database\Seeders\Themes\Travel;

use Database\Seeders\Themes\Main\AnnouncementSeeder as MainAnnouncementSeeder;

class AnnouncementSeeder extends MainAnnouncementSeeder
{
    public function getSettingData(): array
    {
        return [...parent::getSettingData(), 'announcement_text_color' => '#000000'];
    }
}
