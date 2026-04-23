<?php

namespace Database\Seeders\Themes\Main;

use Botble\Blog\Models\Category;
use Botble\Theme\Database\Seeders\ThemeSeeder;
use Botble\Widget\Database\Traits\HasWidgetSeeder;
use Botble\Widget\Widgets\CoreSimpleMenu;
use Illuminate\Support\Str;

class WidgetSeeder extends ThemeSeeder
{
    use HasWidgetSeeder;

    public function run(): void
    {
        $this->createWidgets($this->getData());
    }

    public function getData(): array
    {
        $this->uploadFiles('blog-categories', 'main');
        $this->uploadFiles('icons', 'main');

        return [
            ...$this->getDataBlogBottomSidebar(),
            ...$this->getDataMenuSidebar(),
            ...$this->getDataTopHeaderSidebar(),
            ...$this->getDataPrimarySidebar(),
            ...$this->getDataCustomSidebar(),
            ...$this->getDataCustomSidebar2(),
            ...$this->getDataFooterSidebar(),
            ...$this->getDataHeaderSidebar(),
            ...$this->getDataOtherSidebar(),
        ];
    }

    public function getFollowUsData(): array
    {
        return [
            [
                [
                    'key' => 'title',
                    'value' => '20K Fans',
                ],
                [
                    'key' => 'icon',
                    'value' => 'ti ti-brand-facebook',
                ],
                [
                    'key' => 'url',
                    'value' => 'https://facebook.com',
                ],
            ],
            [
                [
                    'key' => 'title',
                    'value' => '10K Followers',
                ],
                [
                    'key' => 'icon',
                    'value' => 'ti ti-brand-x',
                ],
                [
                    'key' => 'url',
                    'value' => 'https://twitter.com',
                ],
            ],
            [
                [
                    'key' => 'title',
                    'value' => '50K Followers',
                ],
                [
                    'key' => 'icon',
                    'value' => 'ti ti-brand-instagram',
                ],
                [
                    'key' => 'url',
                    'value' => 'https://instagram.com',
                ],
            ],
            [
                [
                    'key' => 'title',
                    'value' => '30K Followers',
                ],
                [
                    'key' => 'icon',
                    'value' => 'ti ti-brand-linkedin',
                ],
                [
                    'key' => 'url',
                    'value' => 'https://linkedin.com',
                ],
            ],
            [
                [
                    'key' => 'title',
                    'value' => '30K Followers',
                ],
                [
                    'key' => 'icon',
                    'value' => 'ti ti-brand-pinterest',
                ],
                [
                    'key' => 'url',
                    'value' => 'https://pinterest.com',
                ],
            ],
            [
                [
                    'key' => 'title',
                    'value' => '04K Subscribers',
                ],
                [
                    'key' => 'icon',
                    'value' => 'ti ti-brand-youtube',
                ],
                [
                    'key' => 'url',
                    'value' => 'https://youtube.com',
                ],
            ],
        ];
    }

    public function getCoreSimpleMenuData(): array
    {
        $data = [];

        $categories = Category::query()
            ->with('slugable')
            ->wherePublished()
            ->limit(5)
            ->get();

        foreach ($categories as $category) {
            $data[] = [
                [
                    'key' => 'label',
                    'value' => $category->name,
                ],
                [
                    'key' => 'url',
                    'value' => route('public.single', Str::slug($category->name)),
                ],
            ];
        }

        return $data;
    }

    public function getDataBlogBottomSidebar(): array
    {
        return [];
    }

    public function getDataMenuSidebar(): array
    {
        return [
            [
                'widget_id' => 'BlogPostsWidget',
                'sidebar_id' => 'menu_sidebar',
                'position' => 1,
                'data' => [
                    'type' => 'recent',
                    'category_ids' => [1, 2, 3],
                    'limit' => 3,
                ],
            ],
            [
                'widget_id' => 'SocialLinksWidget',
                'sidebar_id' => 'menu_sidebar',
                'position' => 2,
                'data' => [],
            ],
        ];
    }

    public function getDataTopHeaderSideBar(): array
    {
        return [
            [
                'widget_id' => 'BreakingNewsWidget',
                'sidebar_id' => 'header_top_sidebar',
                'position' => 1,
                'data' => [
                    'id' => 'BreakingNewsWidget',
                    'image' => $this->filePath('icons/breaking-news-icon.png', 'main'),
                    'type' => 'featured',
                    'category_ids' => [1, 3, 6],
                    'limit' => 4,
                ],
            ],
            [
                'widget_id' => 'DateTimeAndLocalWeather',
                'sidebar_id' => 'header_top_sidebar',
                'position' => 2,
                'data' => [
                    'id' => 'DateTimeAndLocalWeather',
                    'alignment' => 'center',
                ],
            ],
            [
                'widget_id' => 'ActionButtonsWidget',
                'sidebar_id' => 'header_top_sidebar',
                'position' => 4,
                'data' => [
                    'id' => 'ActionButtonsWidget',
                    'enable_newsletter_button' => true,
                    'form_newsletter_button_label' => 'Subscribe',
                    'alignment' => 'end',
                ],
            ],
        ];
    }

    public function getDataPrimarySidebar(): array
    {
        return [
            [
                'widget_id' => 'BlogCategoriesWidget',
                'sidebar_id' => 'primary_sidebar',
                'position' => 1,
                'data' => [
                    'title' => 'Popular Categories',
                    'id' => 'BlogCategoriesWidget',
                    'enable_lazy_loading' => 'yes',
                    'quantity' => '5',
                    'category_id_1' => '1',
                    'background_image_1' => $this->filePath('blog-categories/1.png', 'main'),
                    'background_color_1' => 'rgb(174 81 43)',
                    'category_id_2' => '2',
                    'background_image_2' => $this->filePath('blog-categories/2.png', 'main'),
                    'background_color_2' => 'rgb(102 64 165)',
                    'category_id_3' => '3',
                    'background_image_3' => $this->filePath('blog-categories/3.png', 'main'),
                    'background_color_3' => 'rgb(85 163 168)',
                    'category_id_4' => '4',
                    'background_image_4' => $this->filePath('blog-categories/4.png', 'main'),
                    'background_color_4' => 'rgb(83 39 21)',
                    'category_id_5' => '5',
                    'background_image_5' => $this->filePath('blog-categories/5.png', 'main'),
                    'background_color_5' => 'rgb(85 163 168)',
                ],
            ],
            [
                'widget_id' => 'BlogPostsWidget',
                'sidebar_id' => 'primary_sidebar',
                'position' => 2,
                'data' => [
                    'type' => 'popular',
                    'card_style' => 'default-card',
                    'shape' => 'square',
                    'category_ids' => [1, 3, 4],
                    'title' => 'Top Story',
                    'limit' => 4,
                    'enable_lazy_loading' => 'yes',
                ],
            ],
            [
                'widget_id' => 'FollowUsWidget',
                'sidebar_id' => 'primary_sidebar',
                'position' => 3,
                'data' => [
                    'id' => 'FollowUsWidget',
                    'items' => $this->getFollowUsData(),
                ],
            ],
        ];
    }

    public function getDataCustomSidebar(): array
    {
        return [
            [
                'widget_id' => 'FollowUsWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 1,
                'data' => [
                    'id' => 'FollowUsWidget',
                    'items' => $this->getFollowUsData(),
                ],
            ],
            [
                'widget_id' => 'BlogPostsWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 2,
                'data' => [
                    'id' => 'BlogPostsWidget',
                    'title' => 'New Podcasts',
                    'style' => 'default-card',
                    'shape' => 'circle',
                    'category_ids' => [2, 3],
                    'enable_lazy_loading' => 'yes',
                ],
            ],
            [
                'widget_id' => 'NewsletterWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 3,
                'data' => [
                    'id' => 'NewsletterWidget',
                    'title' => 'Newsletter',
                    'description' => 'Stay Tuned With Updates',
                    'image' => $this->filePath('general/newsletter-image.png', 'main'),
                ],
            ],
        ];
    }

    public function getDataCustomSidebar2(): array
    {
        return [
            [
                'widget_id' => 'BlogPostsWidget',
                'sidebar_id' => 'custom_sidebar_2',
                'position' => 1,
                'data' => [
                    'type' => 'recent',
                    'style' => 'default-card',
                    'shape' => 'square',
                    'category_ids' => [3, 6],
                    'title' => 'Top Story',
                    'limit' => 4,
                ],
            ],
            [
                'widget_id' => 'BlogPostsWidget',
                'sidebar_id' => 'custom_sidebar_2',
                'position' => 2,
                'data' => [
                    'type' => 'popular',
                    'style' => 'orderly-card',
                    'shape' => 'square',
                    'category_ids' => [1, 5, 6],
                    'title' => 'Top Reviews',
                    'limit' => 3,
                ],
            ],
            [
                'widget_id' => 'FollowUsWidget',
                'sidebar_id' => 'custom_sidebar_2',
                'position' => 3,
                'data' => [
                    'id' => 'FollowUsWidget',
                    'items' => $this->getFollowUsData(),
                ],
            ],
            [
                'widget_id' => 'NewsletterWidget',
                'sidebar_id' => 'custom_sidebar_2',
                'position' => 4,
                'data' => [
                    'id' => 'NewsletterWidget',
                    'title' => 'Newsletter',
                    'description' => 'Stay Tuned With Updates',
                    'image' => $this->filePath('general/newsletter-image.png', 'main'),
                ],
            ],
            [
                'widget_id' => 'BannerAdsWidget',
                'sidebar_id' => 'custom_sidebar_2',
                'position' => 5,
                'data' => [
                    'ad_image' => $this->filePath('general/ads-banner.png', 'main'),
                    'ad_link' => 'https://google.com',
                ],
            ],
        ];
    }

    public function getDataFooterSidebar(): array
    {
        return [
            [
                'widget_id' => 'SiteInformationWidget',
                'sidebar_id' => 'footer_sidebar',
                'position' => 1,
                'data' => [
                    'id' => 'SiteInformationWidget',
                    'title' => 'Get In Touch',
                    'email' => 'Info@Demomail.Com',
                    'hotline' => '(00) 236 123 456 88',
                    'address' => '255 Sheet, New Avanew, NY',
                ],
            ],
            [
                'widget_id' => CoreSimpleMenu::class,
                'sidebar_id' => 'footer_sidebar',
                'position' => 2,
                'data' => [
                    'id' => CoreSimpleMenu::class,
                    'name' => 'Most Popular',
                    'style' => '2-column',
                    'items' => $this->getCoreSimpleMenuData(),
                ],
            ],
            [
                'widget_id' => CoreSimpleMenu::class,
                'sidebar_id' => 'footer_sidebar',
                'position' => 3,
                'data' => [
                    'id' => CoreSimpleMenu::class,
                    'name' => 'Help',
                    'items' => [
                        [
                            [
                                'key' => 'label',
                                'value' => 'About',
                            ],
                            [
                                'key' => 'url',
                                'value' => 'about-us',
                            ],
                        ],
                        [
                            [
                                'key' => 'label',
                                'value' => 'Terms of Use',
                            ],
                            [
                                'key' => 'url',
                                'value' => 'terms-of-use',
                            ],
                        ],
                        [
                            [
                                'key' => 'label',
                                'value' => 'Advertise',
                            ],
                            [
                                'key' => 'url',
                                'value' => 'advertise',
                            ],
                        ],
                        [
                            [
                                'key' => 'label',
                                'value' => 'Privacy Policy',
                            ],
                            [
                                'key' => 'url',
                                'value' => 'privacy-policy',
                            ],
                        ],
                        [
                            [
                                'key' => 'label',
                                'value' => 'FAQ',
                            ],
                            [
                                'key' => 'url',
                                'value' => 'faq',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'widget_id' => 'NewsletterWidget',
                'sidebar_id' => 'footer_sidebar',
                'position' => 4,
                'data' => [
                    'id' => 'NewsletterWidget',
                    'title' => 'Newsletter',
                    'description' => 'Register now to get latest updates on promotion & coupons.',
                ],
            ],
        ];
    }

    public function getDataHeaderSidebar(): array
    {
        return [];
    }

    public function getDataOtherSidebar(): array
    {
        return [];
    }
}
