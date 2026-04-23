<?php

namespace Database\Seeders\Themes\Politics;

use Botble\Base\Facades\Html;
use Botble\Page\Models\Page;
use Database\Seeders\Themes\Main\PageSeeder as BasePageSeeder;

class PageSeeder extends BasePageSeeder
{
    public function run(): void
    {
        parent::run();
        $this->uploadFiles('backgrounds', 'politics');

        $homepage = Page::query()->where('name', 'Homepage')->firstOrFail();

        $homepage->update(['content' =>
            Html::tag('div', '[blog-posts style="style-1" limit="5" text_color="#000000" background_color="#fdf2ff"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-6" category_ids="1,2,5" title="EDITOR\'S PICKS" limit="3" title_align="start" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-5" title="Features" limit="8" title_align="start" text_color="transparent" background_color="#FDF2FF" sidebar="custom_sidebar_1"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-9" limit="6" text_color="transparent" background_color="transparent" background_image="' . $this->filePath('backgrounds/slider-bg.png', 'politics') . '"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-12" title="Features" limit="6" title_align="start" text_color="transparent" background_color="transparent" sidebar="custom_sidebar_2"][/blog-posts]') .
            Html::tag('div', '[app-downloads title="Download Our Apps For Stay Update" subtitle="Easy to update latest news, aily podcast and everything in your hand" platform_google_label="Play Store" platform_google_icon="ti ti-brand-google-play" platform_google_url="https://play.google.com/" platform_apple_label="Apple Store" platform_apple_icon="ti ti-brand-apple" platform_apple_url="https://www.apple.com/app-store/" image="' . $this->filePath('general/app-downloads.png', 'main') . '" background_color="#47164F"][/app-downloads]'),
        ]);
    }
}
