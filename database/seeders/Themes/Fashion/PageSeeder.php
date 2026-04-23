<?php

namespace Database\Seeders\Themes\Fashion;

use Botble\Base\Facades\Html;
use Botble\Page\Models\Page;

class PageSeeder extends \Database\Seeders\Themes\Main\PageSeeder
{
    public function run(): void
    {
        parent::run();

        $homepage = Page::query()->where('name', 'Homepage')->firstOrFail();

        $this->uploadFiles('general');
        $this->uploadFiles('banner');

        $homepage->update([
            'content' => Html::tag('div', '[blog-posts style="style-7" category_ids="5,6,7" title="Featured Posts" limit="5"][/blog-posts]') .
                Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-6" category_ids="5,6,7" title="Featured Posts" limit="3" title_align="center" text_color="rgb(255, 255, 255)" background_color="#000000"][/blog-posts]') .
                Html::tag('div', '[blog-categories enable_lazy_loading="yes" style="style-2" category_ids="2,3,4,5,6" title="Most Popular" number_of_post_per_category="8" title_align="start" text_color="transparent" background_color="transparent" sidebar="custom_sidebar_1"][/blog-categories]') .
                Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-10" limit="1" title_align="start" text_color="transparent" background_color="rgb(241, 241, 241)"][/blog-posts]') .
                Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-3" category_ids="2,4,5,6" title="Featured Posts" limit="4" title_align="center" text_color="rgb(255, 255, 255)" background_color="#000000"][/blog-posts]') .
                Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-8" category_ids="5,6,7" title="The Archive" limit="5" title_align="start" text_color="transparent"][/blog-posts]') .
                '<br>' .
                Html::tag('div', '[ads key="UROL9F9ZZVAA"][/ads]') .
                '<br>' .
                Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-9" category_ids="5,6,7" limit="3" title_align="center" text_color="transparent" background_color="transparent"][/blog-posts]') .
                Html::tag('div', '[gallery title="Follow Instagram" limit="6" text_color="#fff" background_color="#181823"][/gallery]'),
        ]);
    }
}
