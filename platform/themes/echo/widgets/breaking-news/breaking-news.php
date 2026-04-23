<?php

use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Blog\Models\Category;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Theme\Echo\FormDecorators\PostByCategoriesFormDecorator;
use Theme\Echo\FormDecorators\PostFormDecorator;
use Theme\Echo\Support\ThemeHelper;

class BreakingNewsWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Breaking News'),
            'description' => __('Breaking News'),
            'image' => null,
            'alignment' => 'start',
        ]);
    }

    public function data(): array|Collection
    {
        if (! is_plugin_active('blog') || Arr::get($this->getConfig(), 'enable_lazy_loading') === 'yes') {
            return ['posts' => collect()];
        }

        $config = $this->getConfig();

        $categoryIds = Arr::get($config, 'category_ids', []) ?: [];
        $limit = (int) Arr::get($config, 'limit', 4);
        $type = Arr::get($config, 'type');

        $posts = ThemeHelper::getBlogPosts($categoryIds, $type, $limit);

        return compact('posts');
    }

    public function adminConfig(): array
    {
        $categories = Category::query()->pluck('name', 'id')->all();
        $categoryIds = explode(',', Arr::get($this->getConfig(), 'category_ids'));
        $types = [
            'latest' => __('Latest'),
            'popular' => __('Popular'),
            'featured' => __('Featured'),
            'recent' => __('Recent'),
        ];

        return compact('categoryIds', 'categories', 'types');
    }

    public function settingForm(): WidgetForm|string|null
    {
        $form = WidgetForm::createFromArray($this->getConfig())
            ->add(
                'image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Image'))
                    ->toArray()
            )
            ->add(
                'delay_time',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(__('Delay time (:from = :to second)', ['from' => 1000, 'to' => 1]))
                    ->defaultValue(2000)
                    ->toArray()
            )
            ->add(
                'alignment',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Alignment'))
                    ->helperText(__('Align the widget'))
                    ->choices([
                        'start' => __('Start'),
                        'center' => __('Center'),
                        'end' => __('End'),
                    ])
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

        return PostFormDecorator::createFrom(PostByCategoriesFormDecorator::createFrom($form, 'image'), 'category_ids');
    }
}
