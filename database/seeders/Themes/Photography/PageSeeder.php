<?php

namespace Database\Seeders\Themes\Photography;

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
            Html::tag('div', '[blog-posts style="style-17" limit="4" text_color="#000000" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-6" title="TIPS OF PHOTOGRAPHY" limit="4" title_align="start" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-18" title="COMMERCIAL PHOTOGRAPHY" limit="4" title_align="start" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-6" title="COMMERCIAL PHOTOGRAPHY" limit="6" title_align="center" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[gallery title="FOLLOW US ON INSTAGRAM" limit="6" text_color="transparent" background_color="transparent"][/gallery]'),
        ]);
    }
}
