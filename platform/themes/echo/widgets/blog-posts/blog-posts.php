<?php

use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Theme\Echo\FormDecorators\PostByCategoriesFormDecorator;
use Theme\Echo\FormDecorators\PostFormDecorator;
use Theme\Echo\Support\ThemeHelper;

class BlogPostsWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Blog Posts'),
            'description' => __('Choose type and categories for posts in widget'),
        ]);
    }

    protected function data(): array|Collection
    {
        $config = $this->getConfig();

        if (Arr::get($config, 'enable_lazy_loading') === 'yes') {
            return [];
        }

        $categoryIds = Arr::get($config, 'category_ids', []) ?: [];
        $limit = (int) Arr::get($config, 'limit', 4);
        $type = Arr::get($config, 'type', 'recent');

        $posts = ThemeHelper::getBlogPosts($categoryIds, $type, $limit);

        $cardStyle = Arr::get($config, 'card_style', 'default-card');

        return compact('posts', 'cardStyle');
    }

    public function settingForm(): WidgetForm|string|null
    {
        $form = WidgetForm::createFromArray($this->getConfig())
            ->add(
                'card_style',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Card style'))
                    ->choices([
                        'default-card' => __('Default card'),
                        'orderly-card' => __('Orderly card'),
                    ])
                    ->toArray()
            )
            ->add(
                'shape',
                SelectField::class,
                SelectFieldOption::make()
                    ->choices([
                        'square' => __('Square image'),
                        'circle' => __('Circle image'),
                    ])
                    ->label(__('Image shape'))
                    ->toArray()
            )
            ->add(
                'enable_lazy_loading',
                SelectField::class,
                SelectFieldOption::make()
                    ->label('Enable lazy loading?')
                    ->choices([
                        'yes' => __('Yes'),
                        'no' => __('No'),
                    ])
                    ->selected(Arr::get($this->getConfig(), 'enable_lazy_loading', 'no'))
                    ->toArray()
            );

        return PostFormDecorator::createFrom(PostByCategoriesFormDecorator::createFrom($form));
    }
}
