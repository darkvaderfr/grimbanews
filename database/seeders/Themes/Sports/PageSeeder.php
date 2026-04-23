<?php

namespace Database\Seeders\Themes\Sports;

use Botble\Base\Facades\Html;
use Botble\Page\Models\Page;
use Database\Seeders\Themes\Main\PageSeeder as BasePageSeeder;

class PageSeeder extends BasePageSeeder
{
    public function run(): void
    {
        parent::run();

        $this->uploadFiles('general', 'sports');
        $this->uploadFiles('backgrounds', 'sports');
        $this->uploadFiles('banner', 'sports');

        $homepage = Page::query()->where('name', 'Homepage')->firstOrFail();

        $homepage->update([
            'content' =>
            Html::tag('div', '[hero-banner title="Sadio Mané moves from Bayern to Al Nassr" subtitle="TOP STORES" banner_image="' . $this->filePath('banner/banner-image.png', 'sports') . '" url="/blog" button_label="Read more" shape_image_1="' . $this->filePath('backgrounds/shape.png', 'sports') . '" shape_image_2="' . $this->filePath('backgrounds/shape-top-news.png', 'sports') . '" background_image="' . $this->filePath('backgrounds/background-hero-banner.png', 'sports') . '" background_color="transparent" text_color="#ffffff"][/hero-banner]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-2" category_ids="1,3,4,6" title="FEATURED STORIES" limit="4" title_align="start" text_color="rgb(255, 255, 255)" background_color="rgb(25, 1, 8)"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-6" category_ids="3,5,7" title="WEEKLY TRAINING" limit="6" title_align="center" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-4" category_ids="3,6" title="WATCH SHOWS" limit="3" title_align="start" text_color="rgb(255, 255, 255)" background_color="rgb(76, 9, 30)"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-5" category_ids="2,5,7" title="WEEKLY TRAINING" limit="8" title_align="center" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[app-downloads title="Download Our Apps For Stay Update" subtitle="Easy to update latest news, aily podcast and everything in your hand" platform_google_label="Play Store" platform_google_icon="ti ti-brand-google-play" platform_google_url="https://play.google.com/" platform_apple_label="Apple Store" platform_apple_icon="ti ti-brand-apple" platform_apple_url="https://www.apple.com/app-store/" image="' . $this->filePath('general/app-downloads.png', 'main') . '" background_image="' . $this->filePath('backgrounds/app-downloads-bg.png', 'sports') . '"][/app-downloads]') .
            Html::tag('div', '[gallery limit="5" text_color="#fff" background_color="#181823"][/gallery]'),
        ]);
    }
}
