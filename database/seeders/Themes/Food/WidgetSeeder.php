<?php

namespace Database\Seeders\Themes\Food;

use Botble\Widget\Models\Widget;
use Botble\Widget\Widgets\CoreSimpleMenu;
use Database\Seeders\Themes\Main\WidgetSeeder as MainWidgetSeeder;

class WidgetSeeder extends MainWidgetSeeder
{
    public function run(): void
    {
        Widget::query()->truncate();

        $this->createWidgets($this->getData());
    }

    public function getDataCustomSidebar(): array
    {
        return [
            [
                'widget_id' => 'ActionButtonsWidget',
                'sidebar_id' => 'header_top_left_sidebar',
                'position' => 1,
                'data' => [
                    'id' => 'ActionButtonsWidget',
                    'enable_search_button' => true,
                    'enable_toggle_side_menu_button' => true,
                    'enable_dark_light_switcher_button' => false,
                    'alignment' => 'start',
                ],
            ],
            [
                'widget_id' => 'ActionButtonsWidget',
                'sidebar_id' => 'header_top_right_sidebar',
                'position' => 1,
                'data' => [
                    'id' => 'ActionButtonsWidget',
                    'enable_dark_light_switcher_button' => true,
                    'enable_search_button' => false,
                    'enable_toggle_side_menu_button' => false,
                    'alignment' => 'end',
                ],
            ],
            [
                'widget_id' => 'SocialLinksWidget',
                'sidebar_id' => 'header_top_right_sidebar',
                'position' => 2,
                'data' => [],
            ],
            [
                'widget_id' => 'ActionButtonsWidget',
                'sidebar_id' => 'header_top_right_sidebar',
                'position' => 3,
                'data' => [
                    'id' => 'ActionButtonsWidget',
                    'enable_dark_light_switcher_button' => false,
                    'enable_search_button' => false,
                    'enable_toggle_side_menu_button' => false,
                    'form_newsletter_button_label' => 'Subscribe',
                    'alignment' => 'end',
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
                'position' => 4,
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
                'position' => 1,
                'data' => [
                    'id' => CoreSimpleMenu::class,
                    'name' => 'Most Popular',
                    'style' => '2-column',
                    'items' => [
                        [
                            [
                                'key' => 'label',
                                'value' => 'Business',
                            ],
                            [
                                'key' => 'url',
                                'value' => '/business',
                            ],
                        ],
                        [
                            [
                                'key' => 'label',
                                'value' => 'Life Style',
                            ],
                            [
                                'key' => 'url',
                                'value' => '/life-style',
                            ],
                        ],
                        [
                            [
                                'key' => 'label',
                                'value' => 'Fashion',
                            ],
                            [
                                'key' => 'url',
                                'value' => 'fashion',
                            ],
                        ],
                        [
                            [
                                'key' => 'label',
                                'value' => 'Travels',
                            ],
                            [
                                'key' => 'url',
                                'value' => 'travels',
                            ],
                        ],
                        [
                            [
                                'key' => 'label',
                                'value' => 'Sports',
                            ],
                            [
                                'key' => 'url',
                                'value' => 'sports',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'widget_id' => CoreSimpleMenu::class,
                'sidebar_id' => 'footer_sidebar',
                'position' => 2,
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
                'widget_id' => CoreSimpleMenu::class,
                'sidebar_id' => 'footer_sidebar',
                'position' => 3,
                'data' => [
                    'id' => CoreSimpleMenu::class,
                    'name' => 'About Us',
                    'items' => $this->getCoreSimpleMenuData(),
                ],
            ],
        ];
    }

    public function getDataTopHeaderSideBar(): array
    {
        return [];
    }
}
