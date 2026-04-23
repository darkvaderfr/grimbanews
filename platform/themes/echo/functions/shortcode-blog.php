<?php

use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\ColorField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\UiSelectorField;
use Botble\Base\Forms\FormFieldOptions;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Theme\Facades\Theme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Arr;
use Theme\Echo\FormDecorators\PostByCategoriesFormDecorator;
use Theme\Echo\FormDecorators\PostFormDecorator;
use Theme\Echo\Support\ThemeHelper;

app('events')->listen(RouteMatched::class, function (): void {
    if (! is_plugin_active('blog')) {
        return;
    }

    Shortcode::register(
        'blog-posts',
        __('Blog Posts'),
        __('Blog Posts'),
        function (ShortcodeCompiler $shortcode): ?string {
            if ($shortcode->enable_lazy_loading === 'yes') {
                return Theme::partial('shortcodes.blog-posts.index', compact('shortcode'));
            }

            $limit = (int) $shortcode->limit ?: 4;
            $categoryIds = Shortcode::fields()->getIds('category_ids', $shortcode);

            $posts = Post::query()
                ->with('slugable')
                ->wherePublished()
                ->when(! empty($categoryIds), function ($query) use ($categoryIds): void {
                    $query->whereHas('categories', function ($query) use ($categoryIds): void {
                        $query->whereIn('id', $categoryIds);
                    });
                })
                ->limit($limit)
                ->latest()
                ->get();

            if ($posts->isEmpty()) {
                return null;
            }

            return Theme::partial('shortcodes.blog-posts.index', compact('shortcode', 'posts'));
        }
    );

    Shortcode::setAdminConfig('blog-posts', function (array $attributes) {
        $sidebars = ThemeHelper::getCustomSidebars();

        $form = ShortcodeForm::createFromArray($attributes)
            ->withLazyLoading()
            ->add(
                'style',
                UiSelectorField::class,
                SelectFieldOption::make()
                    ->choices(
                        collect(range(1, 18))
                            ->mapWithKeys(fn ($number) => [
                                ($style = "style-$number") => [
                                    'label' => __('Style :number', ['number' => $number]),
                                    'image' => Theme::asset()->url("images/shortcodes/blog-posts/$style.png"),
                                ],
                            ])
                            ->toArray()
                    )
                    ->selected(Arr::get($attributes, 'style', 'style-1'))
                    ->toArray()
            )
            ->add(
                'title_align',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Title align'))
                    ->choices([
                        'start' => __('Start'),
                        'center' => __('Center'),
                        'end' => __('End'),
                    ])
                    ->toArray()
            )
            ->add(
                'text_color',
                ColorField::class,
                FormFieldOptions::make()
                    ->label(__('Text color'))
                    ->toArray()
            )
            ->add(
                'background_color',
                ColorField::class,
                FormFieldOptions::make()
                    ->label(__('Background color'))
                    ->defaultValue('transparent')
                    ->toArray()
            )
            ->add(
                'background_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Background image'))
                    ->toArray()
            )
            ->add(
                'sidebar',
                SelectField::class,
                SelectFieldOption::make()
                    ->choices($sidebars)
                    ->label(__('Choose Sidebar'))
                    ->toArray()
            );

        return PostByCategoriesFormDecorator::createFrom(PostFormDecorator::createFrom($form, 'style'), 'style');
    });

    Shortcode::register(
        'blog-categories',
        __('Blog Categories'),
        __('Blog Categories'),
        function (ShortcodeCompiler $shortcode) {
            if ($shortcode->enable_lazy_loading === 'yes') {
                return Theme::partial('shortcodes.blog-categories.index', compact('shortcode'));
            }

            if (! $categoryIds = Shortcode::fields()->getIds('category_ids', $shortcode)) {
                return null;
            }

            $style = $shortcode->style;

            if ($style == 'style-2') {
                Theme::asset()
                    ->container('footer')
                    ->add('blog-categories-tab-js', Theme::asset()->url('js/blog-categories.js'));
            }

            $title = $shortcode->title;
            $firstCategory = null;
            $postsPerCategory = (int) $shortcode->number_of_post_per_category ?: 3;
            $postsPerCategory = max($postsPerCategory, 1);

            if (count($categoryIds) === 1) {
                $firstCategory = Category::query()
                    ->wherePublished()
                    ->with(['children' => fn ($query) => $query->limit(3)])
                    ->where('id', $categoryIds)
                    ->when($style == 'style-3', fn (Builder $query) => $query->withCount('posts'))
                    ->first();

                if (! $firstCategory) {
                    return null;
                }

                $categories = $firstCategory->children;

                $title = $title ?: $firstCategory->name;
            } else {
                $categories = Category::query()
                    ->wherePublished()
                    ->whereIn('id', $categoryIds)
                    ->when($style == 'style-3', fn (Builder $query) => $query->withCount('posts'))
                    ->get();
            }

            if ($categories->isEmpty()) {
                return null;
            }

            return Theme::partial(
                'shortcodes.blog-categories.index',
                compact('shortcode', 'firstCategory', 'categories', 'title', 'postsPerCategory', 'categoryIds')
            );
        }
    );

    Shortcode::setAdminConfig('blog-categories', function (array $attributes) {
        $sidebars = ThemeHelper::getCustomSidebars();

        $form = ShortcodeForm::createFromArray($attributes)
            ->withLazyLoading()
            ->add(
                'style',
                UiSelectorField::class,
                SelectFieldOption::make()
                    ->choices([
                        'style-1' => [
                            'label' => __('Style :number', ['number' => 1]),
                            'image' => Theme::asset()->url('images/shortcodes/blog-categories/style-1.png'),
                        ],
                        'style-2' => [
                            'label' => __('Style :number', ['number' => 2]),
                            'image' => Theme::asset()->url('images/shortcodes/blog-categories/style-2.png'),
                        ],
                        'style-3' => [
                            'label' => __('Style :number', ['number' => 3]),
                            'image' => Theme::asset()->url('images/shortcodes/blog-categories/style-3.png'),
                        ],
                    ])
                    ->selected(Arr::get($attributes, 'style', 'style-1'))
                    ->toArray()
            )
            ->add(
                'number_of_post_per_category',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(__('Number of post per category'))
                    ->defaultValue(3)
                    ->toArray()
            )
            ->add(
                'title_align',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Title align'))
                    ->choices([
                        'start' => __('Start'),
                        'center' => __('Center'),
                        'end' => __('End'),
                    ])
                    ->toArray()
            )
            ->add(
                'text_color',
                ColorField::class,
                FormFieldOptions::make()
                    ->label(__('Text color'))
                    ->toArray()
            )
            ->add(
                'background_color',
                ColorField::class,
                FormFieldOptions::make()
                    ->label(__('Background color'))
                    ->defaultValue('transparent')
                    ->toArray()
            )
            ->add(
                'background_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Background image'))
                    ->toArray()
            )
            ->add(
                'sidebar',
                SelectField::class,
                SelectFieldOption::make()
                    ->choices($sidebars)
                    ->label(__('Choose Sidebar'))
                    ->toArray()
            );

        return PostByCategoriesFormDecorator::createFrom(PostFormDecorator::createFrom($form, 'style', false), 'style');
    });
});
