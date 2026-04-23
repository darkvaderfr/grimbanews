<?php

namespace Database\Seeders\Themes\Gaming;

use Botble\Widget\Models\Widget;
use Botble\Widget\Widgets\CoreSimpleMenu;
use Database\Seeders\Themes\Main\WidgetSeeder as MainWidgetSeeder;

class WidgetSeeder extends MainWidgetSeeder
{
    public function run(): void
    {
        $this->uploadFiles('banner', 'gaming');

        Widget::query()->truncate();

        $this->createWidgets($this->getData());
    }

    public function getDataOtherSidebar(): array
    {
        return [
            [
                'widget_id' => 'NewsletterWidget',
                'sidebar_id' => 'above_footer_sidebar',
                'position' => 1,
                'data' => [
                    'id' => 'NewsletterWidget',
                    'title' => 'Get the gaming world News & Stories into your inbox',
                    'description' => 'By subscribing, you accepted the our Policy.',
                ],
            ],
            [
                'widget_id' => 'BlogPostsWidget',
                'sidebar_id' => 'custom_sidebar_3',
                'position' => 1,
                'data' => [
                    'type' => 'recent',
                    'card_style' => 'orderly-card',
                    'category_ids' => [3, 6, 4],
                    'title' => 'Top Reviews',
                    'limit' => 4,
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
                    'category_ids' => [3, 6],
                    'title' => 'Top Reviews',
                    'limit' => 4,
                ],
            ],
            [
                'widget_id' => 'GalleriesWidget',
                'sidebar_id' => 'custom_sidebar_2',
                'position' => 2,
                'data' => [
                    'id' => 'GalleriesWidget',
                    'title' => 'Gallery',
                    'limit' => 4,
                ],
            ],
        ];
    }

    public function getDataFooterSidebar(): array
    {
        return [
            [
                'widget_id' => 'SocialLinksWidget',
                'sidebar_id' => 'footer_sidebar',
                'position' => 1,
                'data' => [],
            ],
            [
                'widget_id' => CoreSimpleMenu::class,
                'sidebar_id' => 'footer_sidebar',
                'position' => 2,
                'data' => [
                    'id' => CoreSimpleMenu::class,
                    'name' => 'Most Popular',
                    'items' => $this->getCoreSimpleMenuData(),
                ],
            ],
        ];
    }

    public function getDataTopHeaderSideBar(): array
    {
        $this->uploadFiles('icons', 'gaming');

        $data = parent::getDataTopHeaderSideBar();

        $data[0] = [
            'widget_id' => 'BreakingNewsWidget',
            'sidebar_id' => 'header_top_sidebar',
            'position' => 1,
            'data' => [
                'id' => 'BreakingNewsWidget',
                'image' => $this->filePath('icons/breaking-news-icon.png', 'gaming'),
                'type' => 'featured',
                'category_ids' => [1, 3, 6],
                'limit' => 4,
            ],
        ];

        return $data;
    }
}
