<?php

namespace Database\Seeders\Themes\Travel;

use Botble\Base\Facades\Html;
use Botble\Page\Models\Page;
use Database\Seeders\Themes\Main\PageSeeder as BasePageSeeder;

class PageSeeder extends BasePageSeeder
{
    public function run(): void
    {
        parent::run();

        $this->uploadFiles('general', 'travel');
        $this->uploadFiles('videos', 'travel');
        $this->uploadFiles('backgrounds', 'travel');

        $homepage = Page::query()->where('name', 'Homepage')->firstOrFail();

        $homepage->update(['content' =>
            Html::tag('div', '[hero-slider quantity="3" title_1="Explore the Indonesia " media_1="' . $this->filePath('videos/hero-video-1.mp4', 'travel') . '" thumb_1="' . $this->filePath('general/thumb1.png', 'travel') . '" button_url_1="/" button_label_1="Read more" title_2="Explore the Nepal" media_2="' . $this->filePath('videos/hero-video-2.mp4', 'travel') . '" thumb_2="' . $this->filePath('general/thumb2.png', 'travel') . '" button_url_2="/" button_label_2="Read more" title_3="Explore the India" media_3="' . $this->filePath('videos/hero-video-3.mp4', 'travel') . '" thumb_3="' . $this->filePath('general/thumb1.png', 'travel') . '" button_url_3="/" button_label_3="Read more"][/hero-slider]') .
            Html::tag('div', '[blog-categories enable_lazy_loading="yes" style="style-3" category_ids="2,3,5,7" number_of_post_per_category="3" title_align="start" text_color="rgb(255, 255, 255)" background_color="transparent" background_image="' . $this->filePath('backgrounds/latest-news-bg.png', 'travel') . '"][/blog-categories]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-12" limit="6" title_align="start" text_color="transparent" background_color="transparent" sidebar="custom_sidebar_1"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-4" title="Editor\'s Picks" limit="4" title_align="start" text_color="#ffffff" background_color="#714707"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-5" title="Popular Story" limit="6" title_align="start" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[newsletter title="Subscribe to Newsletter" button_label="subscribe" text_color="rgb(255, 255, 255)" background_color="transparent" background_image="' . $this->filePath('backgrounds/newsletter-bg.png', 'travel') . '"][/newsletter]') .
            Html::tag('div', '[gallery limit="5" text_color="#fff" background_color="#181823"][/gallery]'),
        ]);
    }
}
