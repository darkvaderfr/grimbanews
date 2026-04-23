<?php

namespace Database\Seeders\Themes\Technology;

use Botble\Base\Facades\Html;
use Botble\Page\Models\Page;
use Database\Seeders\Themes\Main\PageSeeder as BasePageSeeder;

class PageSeeder extends BasePageSeeder
{
    public function run(): void
    {
        parent::run();

        $homepage = Page::query()->where('name', 'Homepage')->firstOrFail();

        $this->uploadFiles('backgrounds');
        $this->uploadFiles('banner');
        $this->uploadFiles('blog-categories');

        $homepage->update([
            'content' =>
                Html::tag('div', '[blog-posts style="style-11" category_ids="5,6" title="Top Stories" limit="4" title_align="start" text_color="rgb(255, 255, 255)" background_color="transparent" background_image="' . $this->filePath('backgrounds/hero-background.png', 'technology') . '"][/blog-posts]') .
                Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-2" category_ids="2,4,6" title="Latest News" limit="3" title_align="start" text_color="transparent" background_color="transparent"][/blog-posts]') .
                Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-12" category_ids="2,5" title="EDITOR PICK" limit="3" title_align="start" text_color="#ffffff" background_color="rgb(120, 23, 242)" background_image="' . $this->filePath('backgrounds/feature-bg-shape.png', 'technology') . '" sidebar="custom_sidebar_1"][/blog-posts]') .
                Html::tag('div', '[blog-categories enable_lazy_loading="yes" style="style-3" category_ids="2,3,4,5,6" title="Discover Categories/" number_of_post_per_category="3" title_align="start" text_color="#ffffff" background_color="transparent"][/blog-categories]') .
                Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-4" category_ids="3,7" title="Most Popular /" limit="6" title_align="center" text_color="transparent" background_color="rgb(249, 249, 249)"][/blog-posts]') .
                '<br>' .
                Html::tag('div', '[ads key="UROL9F9ZZVAA"][/ads]') .
                '<br>' .
                Html::tag('div', '[blog-categories enable_lazy_loading="yes" style="style-1" category_ids="2,4,6" number_of_post_per_category="4" text_color="#ffffff" background_color="transparent" background_image="' . $this->filePath('backgrounds/video-bg.png', 'technology') . '"][/blog-categories]') .
                Html::tag('div', '[app-downloads title="Download Our Apps For Stay Update" subtitle="Easy to update latest news, aily podcast and everything in your hand" platform_google_label="Play Store" platform_google_icon="ti ti-brand-google-play" platform_google_url="https://play.google.com/" platform_apple_label="Apple Store" platform_apple_icon="ti ti-brand-apple" platform_apple_url="https://www.apple.com/app-store/" image="' . $this->filePath('general/app-downloads.png', 'main') . '" background_color="#7817F2"][/app-downloads]'),
        ]);
    }
}
