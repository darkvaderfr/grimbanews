<?php

use Botble\Base\Contracts\BaseModel;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\RepeaterFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\RepeaterField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Blog\Forms\CategoryForm;
use Botble\Blog\Http\Requests\PostRequest;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Media\Facades\RvMedia;
use Botble\Member\Forms\MemberForm;
use Botble\Member\Models\Member;
use Botble\Newsletter\Facades\Newsletter;
use Botble\Page\Models\Page;
use Botble\Slug\Facades\SlugHelper;
use Botble\Support\Http\Requests\Request as BaseRequest;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Supports\ThemeSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

if (is_plugin_active('member')) {
    SlugHelper::registerModule(Member::class, 'Member');
}

app()->booted(function (): void {
    ThemeSupport::registerToastNotification();
    ThemeSupport::registerPreloader();
    ThemeSupport::registerSocialLinks();
    ThemeSupport::registerSiteCopyright();
    ThemeSupport::registerLazyLoadImages();
    ThemeSupport::registerDateFormatOption();

    if (is_plugin_active('newsletter')) {
        Newsletter::registerNewsletterPopup(true);
    }

    RvMedia::addSize('large', 760, 430)
        ->addSize('extra-large', 860, 630)
        ->addSize('medium-square', 360, 360)
        ->addSize('medium', 550, 270)
        ->addSize('thumb-medium', 550, 420)
        ->addSize('small', 360, 250);

    register_sidebar([
        'id' => 'primary_sidebar',
        'name' => __('Primary sidebar'),
        'description' => __('Blog sidebar'),
    ]);

    register_sidebar([
        'id' => 'header_top_sidebar',
        'name' => __('Header top sidebar'),
        'description' => __('Header top sidebar'),
    ]);

    register_sidebar([
        'id' => 'menu_sidebar',
        'name' => __('Menu sidebar'),
        'description' => __('Menu sidebar'),
    ]);

    register_sidebar([
        'id' => 'footer_sidebar',
        'name' => __('Footer sidebar'),
        'description' => __('Footer sidebar'),
    ]);

    register_sidebar([
        'id' => 'blog_bottom_sidebar',
        'name' => __('Blog Bottom sidebar'),
        'description' => __('Blog Bottom sidebar'),
    ]);

    register_sidebar([
        'id' => 'custom_sidebar_1',
        'name' => __('Custom sidebar 1'),
        'description' => __('Sidebar for UI Blocks'),
    ]);

    register_sidebar([
        'id' => 'custom_sidebar_2',
        'name' => __('Custom sidebar 2'),
        'description' => __('Sidebar for UI Blocks'),
    ]);

    register_page_template([
        'homepage' => __('Homepage'),
        'full-width' => __('Full Width'),
    ]);

    if (is_plugin_active('member')) {
        MemberForm::extend(function (MemberForm $form) {
            return $form
                ->addAfter(
                    'description',
                    'title',
                    TextField::class,
                    TextFieldOption::make()
                        ->label(__('Title'))
                        ->placeholder(__('Enter title'))
                        ->metadata()
                        ->toArray()
                )
                ->addAfter(
                    'title',
                    'social_links',
                    RepeaterField::class,
                    RepeaterFieldOption::make()
                        ->colspan(2)
                        ->fields(Theme::getSocialLinksRepeaterFields())
                        ->label(__('Social links'))
                        ->metadata()
                        ->toArray()
                );
        });
    }

    if (is_plugin_active('blog')) {
        CategoryForm::extend(function (CategoryForm $form) {
            return $form
                ->addAfter(
                    'description',
                    'image',
                    MediaImageField::class,
                    MediaImageFieldOption::make()
                        ->label(__('Image'))
                        ->metadata()
                        ->toArray()
                );

        });
    }

    add_filter('core_request_rules', function (array $rules, BaseRequest $request) {
        if ($request instanceof PostRequest) {
            return array_merge($rules, [
                'breadcrumb_background_image' => 'nullable|string',
                'breadcrumb_background_color' => 'nullable|string',
                'breadcrumb_text_color' => 'nullable|string',
                'video_url' => 'nullable|url',
                'audio' => 'nullable|string',
                'time_to_read' => 'nullable|numeric',
            ]);
        }

        return $rules;
    }, 120, 2);

    add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function (FormAbstract $form, BaseModel $data): FormAbstract {
        switch ($data::class) {
            case Page::class:
                $data->loadMissing('metadata');

                $form
                    ->when(theme_option('theme_breadcrumb_enabled', true), function (FormAbstract $form) use ($data): void {
                        $form
                            ->addAfter('image', 'breadcrumb_background_image', 'mediaImage', [
                                'label' => __('Breadcrumb background (1920x300px)'),
                                'value' => $data->getMetaData('breadcrumb_background_image', true),
                            ])
                            ->addAfter('breadcrumb_background_image', 'breadcrumb_background_color', 'customColor', [
                                'label' => __('Breadcrumb background color'),
                                'value' => $data->getMetaData('breadcrumb_background_color', true),
                            ])
                            ->addAfter('breadcrumb_background_image', 'breadcrumb_text_color', 'customColor', [
                                'label' => __('Breadcrumb text color'),
                                'value' => $data->getMetaData('breadcrumb_text_color', true),
                            ]);
                    });

                break;

            case Post::class:
                $data->loadMissing('metadata');
                $form
                    ->addAfter(
                        'image',
                        'time_to_read',
                        NumberField::class,
                        NumberFieldOption::make()
                            ->label(__('Time to read (minute)'))
                            ->value($data->getMetaData('time_to_read', true))
                            ->toArray()
                    )
                    ->addAfter('content', 'video_url', 'text', [
                        'label' => __('Video URL'),
                        'value' => $data->getMetaData('video_url', true),
                        'attr' => [
                            'placeholder' => __('Enter Video URL, e.g: https://www.youtube.com/watch?v=xxxxxx'),
                        ],
                    ])
                    ->addAfter('video_url', 'audio', 'mediaFile', [
                        'label' => __('Audio'),
                        'value' => $data->getMetaData('audio', true),
                        'help_block' => [
                            'text' => __('Only accepts file of type: mp3.'),
                            'tag' => 'small',
                            'attr' => [
                                'class' => 'form-hint',
                            ],
                        ],
                    ]);

                break;

            case Category::class:
                $data->loadMissing('metadata');

                $form->modify('icon', 'themeIcon');

                break;
        }

        return $form;
    }, 120, 2);

    add_action(
        [BASE_ACTION_AFTER_CREATE_CONTENT, BASE_ACTION_AFTER_UPDATE_CONTENT],
        function (string $type, Request $request, BaseModel $model): void {
            $fields = [];

            switch ($model::class) {
                case Page::class:
                    $fields = [
                        'breadcrumb_background_image',
                        'breadcrumb_background_color',
                        'breadcrumb_text_color',
                    ];

                    // no break
                case Post::class:
                    if ($model::class === Post::class) {
                        $fields = [
                            ...$fields,
                            'video_url',
                            'audio',
                            'time_to_read',
                        ];
                    }

                    $model->saveMetaDataFromFormRequest($fields, $request);

                    break;
            }
        },
        120,
        3
    );

    add_filter('core_whitelist_shortcodes', function (array $shortcodes) {
        return [
            ...$shortcodes,
            'content-listing-style',
            'content-capitalize',
            'content-image',
            'content-quote',
        ];
    }, 128);

    add_filter('ads_locations', function (array $locations) {
        return [
            ...$locations,
            'main_content_before' => __('Main Content (before)'),
            'main_content_after' => __('Main Content (after)'),
            'primary_sidebar_before' => __('Primary Sidebar (before)'),
            'primary_sidebar_after' => __('Primary Sidebar (after)'),
            'footer_before' => __('Footer (before)'),
            'footer_after' => __('Footer (after)'),
            'post_list_before' => __('Post List (before)'),
            'post_list_after' => __('Post List (after)'),
            'post_before' => __('Post Detail (before)'),
            'post_after' => __('Post Detail (after)'),
        ];
    }, 128);
});
