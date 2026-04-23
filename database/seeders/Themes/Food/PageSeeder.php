<?php

namespace Database\Seeders\Themes\Food;

use Botble\Base\Facades\Html;
use Botble\Page\Models\Page;
use Database\Seeders\Themes\Main\PageSeeder as BasePageSeeder;

class PageSeeder extends BasePageSeeder
{
    public function run(): void
    {
        parent::run();

        $this->uploadFiles('backgrounds', 'food');

        $homepage = Page::query()->where('name', 'Homepage')->firstOrFail();

        $homepage->update(['content' =>
            Html::tag('div', '[blog-posts style="style-7" limit="6" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-10" limit="1" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-6" category_ids="3,6,7" title="Our Newest Recipes" limit="6" title_align="center" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-4" title="Food Reviews" limit="5" title_align="start" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-6" category_ids="2,4,6" title="Perfect For Your Home" limit="3" title_align="center" text_color="transparent" background_color="transparent"][/blog-posts]') .
            Html::tag('div', '[newsletter title="What’s More Echo Food Blog" subtitle="Our best tips for eating thoughtfully and living joyfully, right to your inbox." button_label="Subscribe" text_color="rgb(255, 255, 255)" background_color="rgb(0, 56, 17)"][/newsletter]') .
            Html::tag('div', '[follow-us title="Join Our Community" subtitle="Follow us @echo_food" text_color="#181823" background_color="transparent" background_image="' . $this->filePath('backgrounds/follow-us-bg.png', 'food') . '" quantity="6" title_1="Facebook" icon_1="ti ti-brand-facebook" url_1="/facebook" title_2="Twitter" icon_2="ti ti-brand-x" title_3="Youtube" icon_3="ti ti-brand-youtube" url_3="/" title_4="Linkedin" icon_4="ti ti-brand-linkedin" url_4="https://www.linkedin.com/" title_5="Instagram" icon_5="ti ti-brand-instagram" url_5="https://www.instagram.com/" title_6="Pinterest" icon_6="ti ti-brand-pinterest" url_6="https://www.pinterest.com/"][/follow-us]'),
        ]);
    }
}
