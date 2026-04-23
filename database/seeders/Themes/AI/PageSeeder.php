<?php

namespace Database\Seeders\Themes\AI;

use Botble\Base\Facades\Html;
use Botble\Page\Models\Page;
use Database\Seeders\Themes\Main\PageSeeder as BasePageSeeder;

class PageSeeder extends BasePageSeeder
{
    public function run(): void
    {
        parent::run();

        $homepage = Page::query()->where('name', 'Homepage')->firstOrFail();

        $homepage->update(['content' =>
            Html::tag('div', '[blog-posts style="style-14" limit="7" text_color="rgb(0, 0, 0)" background_color="rgb(255, 255, 255)"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-15" category_ids="3,4,6,7" title="Latest Updates" limit="4" title_align="center" text_color="transparent" background_color="rgb(159, 104, 245)"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-13" title="Trending News" limit="6" title_align="start" text_color="transparent" background_color="transparent" sidebar="custom_sidebar_1"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-4" title="Editor\'s Picks" limit="4" title_align="center" text_color="#ffffff" background_color="rgb(159, 104, 245)"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-2" category_ids="1,4,6,7" title="AI for Business" limit="6" title_align="start" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-6" title="Popular Of the Weeks" limit="4" title_align="start" text_color="rgb(0, 0, 0)" background_color="rgb(242, 242, 242)"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-16" category_ids="2,5,7" title="AI for Business" limit="3" title_align="start" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[app-downloads title="Download Our Apps For Stay Update" subtitle="Easy to update latest news, aily podcast and everything in your hand" platform_google_label="Play Store" platform_google_icon="ti ti-brand-google-play" platform_google_url="https://play.google.com/" platform_apple_label="Apple Store" platform_apple_icon="ti ti-brand-apple" platform_apple_url="https://www.apple.com/app-store/" image="' . $this->filePath('general/app-downloads.png', 'main') . '" background_color="#9f68f5"][/app-downloads]'),
        ]);
    }
}
