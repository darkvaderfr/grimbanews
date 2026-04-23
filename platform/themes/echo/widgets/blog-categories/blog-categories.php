<?php

use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Blog\Models\Category;
use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Shortcode\Forms\FieldOptions\ShortcodeTabsFieldOption;
use Botble\Shortcode\Forms\Fields\ShortcodeTabsField;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class BlogCategoriesWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Blog Categories'),
            'description' => __('Blog Categories Widget'),
        ]);
    }

    protected function settingForm(): WidgetForm|string|null
    {
        if (! is_plugin_active('blog')) {
            return null;
        }

        $categories = Category::query()->wherePublished()->pluck('name', 'id')->all();

        return WidgetForm::createFromArray($this->getConfig())
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Title'))
                    ->toArray()
            )
            ->add(
                'tabs',
                ShortcodeTabsField::class,
                ShortcodeTabsFieldOption::make()
                    ->attrs($this->getConfig())
                    ->fields([
                        'category_id' => [
                            'type' => 'select',
                            'options' => $categories,
                            'title' => __('Category'),
                            'required' => true,
                        ],
                        'title' => [
                            'title' => __('Display title'),
                        ],
                        'background_image' => [
                            'type' => 'image',
                            'title' => __('Background image'),
                        ],
                        'background_color' => [
                            'type' => 'color',
                            'title' => __('Background color'),
                            'value' => 'transparent',
                        ],
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
    }

    protected function data(): array|Collection
    {
        $config = $this->getConfig();

        if (Arr::get($config, 'enable_lazy_loading') === 'yes') {
            return [];
        }

        $title = Arr::get($config, 'title');

        $tabs = Shortcode::fields()->getTabsData([
            'background_image', 'background_color', 'category_id', 'title',
        ], new ShortcodeCompiler('blog-categories', $config));

        $tabs = collect($tabs);

        $categoryIds = array_filter($tabs->pluck('category_id')->all());

        if (empty($categoryIds)) {
            return ['title' => $title, 'tabs' => []];
        }

        $categories = Category::query()
            ->with('slugable')
            ->wherePublished()
            ->whereIn('id', $categoryIds)
            ->get()
            ->keyBy('id')
            ->all();

        if (! $categories) {
            return [];
        }

        $tabs->transform(fn ($tab) => [
            'category' => $categories[$tab['category_id']] ?? null,
            'title' => $tab['title'],
            'background_image' => $tab['background_image'],
            'background_color' => $tab['background_color'],
        ]);

        return compact('title', 'tabs');
    }
}
