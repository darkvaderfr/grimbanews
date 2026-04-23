<?php

namespace Database\Seeders\Themes\Gaming;

use Botble\Base\Facades\Html;
use Botble\Page\Models\Page;
use Database\Seeders\Themes\Main\PageSeeder as BasePageSeeder;

class PageSeeder extends BasePageSeeder
{
    public function run(): void
    {
        parent::run();

        $homepage = Page::query()->where('name', 'Homepage')->firstOrFail();

        $this->uploadFiles('backgrounds', 'gaming');

        $homepage->update(['content' =>
            Html::tag('div', '[blog-posts style="style-1" limit="5" text_color="transparent" background_color="transparent" background_image="' . $this->filePath('backgrounds/hero-bg.png', 'gaming') . '"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-2" title="Trending Now" limit="3" title_align="start" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-5" category_ids="3,6,7" title="Features" limit="5" title_align="start" text_color="rgb(255, 255, 255)" background_color="rgb(24, 24, 35)" sidebar="custom_sidebar_1"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-13" title="Editor\'s Pick" limit="4" title_align="start" text_color="transparent" background_color="transparent" sidebar="custom_sidebar_2"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-9" limit="6" text_color="rgb(255, 255, 255)" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-12" category_ids="2,3,5" title="Tip\'s & Guides" limit="3" title_align="start" text_color="transparent" background_color="transparent" sidebar="custom_sidebar_3"][/blog-posts]') .
            Html::tag('div', '[app-downloads title="Download Our Apps For Stay Update" subtitle="Easy to update latest news, aily podcast and everything in your hand" platform_google_label="Play Store" platform_google_icon="ti ti-brand-google-play" platform_google_url="https://play.google.com/" platform_apple_label="Apple Store" platform_apple_icon="ti ti-brand-apple" platform_apple_url="https://www.apple.com/app-store/" image="' . $this->filePath('general/app-downloads.png', 'main') . '" background_image="' . $this->filePath('backgrounds/apps-download-bg.png', 'gaming') . '" background_color="rgb(255, 73, 98)"][/app-downloads]'),
        ]);
    }
}
