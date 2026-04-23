<?php

namespace Database\Seeders\Themes\Main;

use Botble\Base\Supports\BaseSeeder;
use Botble\Blog\Models\Post;
use Botble\Menu\Database\Traits\HasMenuSeeder;
use Botble\Page\Database\Traits\HasPageSeeder;

class MenuSeeder extends BaseSeeder
{
    use HasMenuSeeder;
    use HasPageSeeder;

    public function run(): void
    {
        $childBlog = [];

        $postStyles = ['card', 'list', 'grid', 'mixed'];

        foreach ($postStyles as $style) {
            $childBlog[] = [
                'title' => 'Blog ' . ucfirst($style),
                'url' => '/blog?style=' . $style,
            ];
        }

        $data = [
            [
                'name' => 'Main menu',
                'slug' => 'main-menu',
                'location' => 'main-menu',
                'items' => [
                    [
                        'title' => 'Home',
                        'url' => '/',
                        'children' => [
                            [
                                'title' => 'Home Sports',
                                'url' => 'https://echo-sports.archielite.com',
                            ],
                            [
                                'title' => 'Home Travel',
                                'url' => 'https://echo-travel.archielite.com',
                            ],
                            [
                                'title' => 'Home AI',
                                'url' => 'https://echo-ai.archielite.com',
                            ],
                            [
                                'title' => 'Home Politics',
                                'url' => 'https://echo-politics.archielite.com',
                            ],
                            [
                                'title' => 'Home Food',
                                'url' => 'https://echo-food.archielite.com',
                            ],
                            [
                                'title' => 'Home Photography',
                                'url' => 'https://echo-photography.archielite.com',
                            ],
                            [
                                'title' => 'Home News',
                                'url' => 'https://echo.archielite.com',
                            ],
                            [
                                'title' => 'Home Fashion',
                                'url' => 'https://echo-fashion.archielite.com',
                            ],
                            [
                                'title' => 'Home Technology',
                                'url' => 'https://echo-technology.archielite.com',
                            ],
                            [
                                'title' => 'Home Gaming',
                                'url' => 'https://echo-gaming.archielite.com',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Pages',
                        'url' => '/',
                        'children' => [
                            [
                                'title' => 'About',
                                'url' => '/about-us',
                            ],
                            [
                                'title' => 'Author',
                                'url' => '/author/smith',
                            ],
                            [
                                'title' => 'Team',
                                'url' => '/team',
                            ],
                            [
                                'title' => 'Error Page',
                                'url' => '/error-page',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Blog',
                        'url' => '/blog',
                        'children' => $childBlog,
                    ],
                    [
                        'title' => 'Post',
                        'reference_type' => Post::class,
                        'reference_id' => 1,
                    ],
                    [
                        'title' => 'Contact',
                        'url' => 'contact',
                    ],
                ],
            ],
        ];

        $this->createMenus($data);
    }
}
