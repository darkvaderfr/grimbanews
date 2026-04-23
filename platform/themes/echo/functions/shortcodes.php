<?php

use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\InputFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\ColorField;
use Botble\Base\Forms\Fields\CoreIconField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\Fields\UiSelectorField;
use Botble\Base\Forms\FormFieldOptions;
use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Shortcode\Forms\FieldOptions\ShortcodeTabsFieldOption;
use Botble\Shortcode\Forms\Fields\ShortcodeTabsField;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Supports\ThemeSupport;
use Botble\Theme\Supports\Youtube;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Arr;

app('events')->listen(RouteMatched::class, function (): void {
    ThemeSupport::registerGoogleMapsShortcode();
    ThemeSupport::registerYoutubeShortcode();

    Shortcode::register('app-downloads', __('App Downloads'), __('App Downloads'), function (ShortcodeCompiler $shortcode): ?string {
        return Theme::partial('shortcodes.app-downloads.index', compact('shortcode'));
    });

    Shortcode::setPreviewImage('app-downloads', Theme::asset()->url('images/ui-blocks/app-downloads.png'));

    Shortcode::setAdminConfig('app-downloads', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Title'))
                    ->toArray()
            )
            ->add(
                'subtitle',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Subtitle'))
                    ->toArray()
            )
            ->add(
                'platform_google_label',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Platform Google Label'))
                    ->placeholder(__('Enter platform Google label'))
                    ->toArray()
            )
            ->add(
                'platform_google_icon',
                CoreIconField::class,
                FormFieldOptions::make()
                    ->label(__('Platform Google Icon'))
                    ->toArray()
            )
            ->add(
                'platform_google_url',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Platform Google URL'))
                    ->placeholder(__('Enter platform Google URL'))
                    ->toArray()
            )
            ->add(
                'platform_apple_label',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Platform Apple Label'))
                    ->placeholder(__('Enter platform Apple label'))
                    ->toArray()
            )
            ->add(
                'platform_apple_icon',
                CoreIconField::class,
                FormFieldOptions::make()
                    ->label(__('Platform Apple Icon'))
                    ->toArray()
            )
            ->add(
                'platform_apple_url',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Platform Apple URL'))
                    ->placeholder(__('Enter platform Apple URL'))
                    ->toArray()
            )
            ->add(
                'image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Image'))
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
                'background_color',
                ColorField::class,
                FormFieldOptions::make()
                    ->label(__('Background color'))
                    ->defaultValue(theme_option('primary_color'))
                    ->toArray()
            );
    });

    Shortcode::register('about-us', __('About us'), __('About us'), function (ShortcodeCompiler $shortcode): ?string {
        return Theme::partial('shortcodes.about-us.index', compact('shortcode'));
    });

    Shortcode::setPreviewImage('about-us', Theme::asset()->url('images/ui-blocks/about-us.png'));

    Shortcode::setAdminConfig('about-us', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Title'))
                    ->toArray()
            )
            ->add(
                'subtitle',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Subtitle'))
                    ->toArray()
            )
            ->add(
                'description',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->toArray()
            )
            ->add(
                'staff_information_open',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content(
                        '<div class="form-fieldset">' . sprintf('<legend class="fs-3 fw-medium">%s</legend>', __('Staff information'))
                    )
                    ->toArray()
            )
            ->add(
                'staff_name',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Name'))
                    ->toArray()
            )
            ->add(
                'staff_description',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->toArray()
            )
            ->add(
                'staff_signature',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Staff signature'))
                    ->toArray()
            )
            ->add(
                'staff_information_closed',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('</div')
                    ->toArray()
            )
            ->add(
                'first_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Image :number', ['number' => 1]))
                    ->toArray()
            )
            ->add(
                'second_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Image :number', ['number' => 2]))
                    ->toArray()
            );
    });

    Shortcode::register('intro-video', __('Intro video'), __('Intro video'), function (ShortcodeCompiler $shortcode): ?string {
        $shortcode->video_id = Youtube::getYoutubeVideoID($shortcode->video_url);

        return Theme::partial('shortcodes.intro-video.index', compact('shortcode'));
    });

    Shortcode::setAdminConfig('intro-video', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->add(
                'image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label('Image')
                    ->toArray()
            )
            ->add(
                'video_url',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('YouTube URL'))
                    ->toArray()
            )
            ->add(
                'play_icon',
                CoreIconField::class,
                SelectFieldOption::make()
                    ->label(__('Play icon'))
                    ->toArray()
            );
    });

    Shortcode::register('content-listing-style', __('Content listing style'), __('Content listing style'), function (ShortcodeCompiler $shortcode): ?string {
        $tabs = Shortcode::fields()->getTabsData(['item'], $shortcode);

        return Theme::partial('shortcodes.content-listing-style.index', compact('shortcode', 'tabs'));
    });

    Shortcode::setAdminConfig('content-listing-style', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->add(
                'number_of_columns',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Number of columns'))
                    ->addAttribute('class', 'shortcode-field-select-style')
                    ->choices([
                        'col-1' => __('1 Column'),
                        'col-2' => __('2 Columns'),
                    ])
                    ->toArray()
            )
            ->add(
                'tabs',
                ShortcodeTabsField::class,
                ShortcodeTabsFieldOption::make()
                    ->attrs($attributes)
                    ->fields([
                        'item' => [
                            'title' => __('Content'),
                            'required' => true,
                        ],
                    ])
                    ->toArray()
            );
    });

    Shortcode::register('content-capitalize', __('Content Capitalize'), __('Content Capitalize'), function (ShortcodeCompiler $shortcode): ?string {
        return Theme::partial('shortcodes.content-capitalize.index', compact('shortcode'));
    });

    Shortcode::setAdminConfig('content-capitalize', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->add(
                'text',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(__('Content'))
                    ->toArray()
            );
    });

    Shortcode::register('content-image', __('Content image'), __('Content image'), function (ShortcodeCompiler $shortcode): ?string {
        $tabs = Shortcode::fields()->getTabsData(['image', 'caption'], $shortcode);

        return Theme::partial('shortcodes.content-image.index', compact('shortcode', 'tabs'));
    });

    Shortcode::setAdminConfig('content-image', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->add(
                'number_of_columns',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Number of columns'))
                    ->choices([
                        'col-1' => __(':number Column', ['number' => 1]),
                        'col-2' => __(':number Columns', ['number' => 2]),
                        'col-3' => __(':number Columns', ['number' => 3]),
                        'col-4' => __(':number Columns', ['number' => 4]),
                        'col-5' => __(':number Columns', ['number' => 5]),
                        'col-6' => __(':number Columns', ['number' => 6]),
                    ])
                    ->addAttribute('class', 'shortcode-field-select-style')
                    ->toArray()
            )
            ->add(
                'tabs',
                ShortcodeTabsField::class,
                ShortcodeTabsFieldOption::make()
                    ->attrs($attributes)
                    ->fields([
                        'image' => [
                            'type' => 'image',
                            'title' => __('Image'),
                            'required' => true,
                        ],
                        'caption' => [
                            'title' => __('Caption'),
                            'required' => true,
                        ],
                    ])
                    ->toArray()
            );
    });

    Shortcode::register('content-quote', __('Content quote'), __('Content quote'), function (ShortcodeCompiler $shortcode): ?string {
        return Theme::partial('shortcodes.content-quote.index', compact('shortcode'));
    });

    Shortcode::setAdminConfig('content-quote', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->add(
                'style',
                UiSelectorField::class,
                SelectFieldOption::make()
                    ->choices([
                        'style-1' => [
                            'label' => __('Quote full width'),
                        ],
                        'style-2' => [
                            'label' => __('Quote with description'),
                        ],
                    ])
                    ->selected(Arr::get($attributes, 'style', 'style-1'))
                    ->addAttribute('class', 'shortcode-field-select-style')
                    ->toArray()
            )
            ->add(
                'quote',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Quote'))
                    ->toArray()
            )
            ->add(
                'author',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Author'))
                    ->toArray()
            )
            ->add(
                'shortcode_fields_of_quote_with_description_open',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<div class="d-none shortcode-field-style-item" data-styles="2">')
                    ->toArray()
            )
            ->add(
                'description',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(__('Description'))
                    ->toArray()
            )
            ->add(
                'top_left_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Top Left Image'))
                    ->toArray()
            )
            ->add(
                'shortcode_fields_of_quote_with_description_close',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('</div>')
                    ->toArray()
            );
    });

    Shortcode::register('hero-banner', __('Hero Banner'), __('Hero Banner'), function (ShortcodeCompiler $shortcode): ?string {
        return Theme::partial('shortcodes.hero-banner.index', compact('shortcode'));
    });

    Shortcode::setPreviewImage('hero-banner', Theme::asset()->url('images/ui-blocks/hero-banner.png'));

    Shortcode::setAdminConfig('hero-banner', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Title'))
                    ->toArray()
            )
            ->add(
                'subtitle',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Subtitle'))
                    ->toArray()
            )
            ->add(
                'banner_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Banner image'))
                    ->toArray()
            )
            ->add(
                'url',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('URL'))
                    ->toArray()
            )
            ->add(
                'button_label',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Button label'))
                    ->toArray()
            )
            ->add(
                'shape_image_1',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Shape image :number', ['number' => 1]))
                    ->toArray()
            )
            ->add(
                'shape_image_2',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Shape image :number', ['number' => 2]))
                    ->toArray()
            )
            ->add(
                'text_color',
                ColorField::class,
                InputFieldOption::make()
                    ->label(__('Text color'))
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
                'background_color',
                ColorField::class,
                InputFieldOption::make()
                    ->label(__('Background color'))
                    ->toArray()
            );
    });

    Shortcode::register('hero-slider', __('Hero Slider'), __('Hero Slider'), function (ShortcodeCompiler $shortcode): ?string {
        $tabs = Shortcode::fields()->getTabsData(['title', 'media', 'thumb', 'button_url', 'button_label'], $shortcode);

        return Theme::partial('shortcodes.hero-slider.index', compact('shortcode', 'tabs'));
    });

    Shortcode::setPreviewImage('hero-slider', Theme::asset()->url('images/ui-blocks/hero-slider.png'));

    Shortcode::setAdminConfig('hero-slider', function (array $attributes) {
        $fields = [
            'title' => [
                'type' => 'text',
                'title' => __('Title'),
            ],
            'media' => [
                'type' => 'file',
                'title' => __('Media'),
                'required' => true,
            ],
            'thumb' => [
                'type' => 'image',
                'title' => __('Thumb image'),
                'required' => true,
            ],
            'button_url' => [
                'type' => 'text',
                'title' => __('Button URL'),
            ],
            'button_label' => [
                'type' => 'text',
                'title' => __('Button label'),
            ],
        ];

        return ShortcodeForm::createFromArray($attributes)
            ->add(
                'tabs',
                ShortcodeTabsField::class,
                ShortcodeTabsFieldOption::make()
                    ->fields($fields)
                    ->attrs($attributes)
                    ->toArray()
            )
            ->add(
                'text_color',
                ColorField::class,
                InputFieldOption::make()
                    ->label(__('Text color'))
                    ->toArray()
            );
    });

    Shortcode::register('follow-us', __('Follow Us'), __('Follow Us'), function (ShortcodeCompiler $shortcode): ?string {
        $backgroundColor = echo_check_transparent_color($shortcode->background_color) ? null : $shortcode->background_color;
        $backgroundImage = $shortcode->background_image ? RvMedia::url($shortcode->background_image) : null;
        $textColor = $shortcode->text_color
            ? (echo_check_transparent_color($shortcode->text_color) ? '#ffffff' : $shortcode->text_color)
            : '#ffffff';

        $variableStyles = [
            "--background-color: $backgroundColor" => $backgroundColor,
            "--background-image: url('$backgroundImage')" => $backgroundImage,
            "--text-color: $textColor" => $textColor,
        ];

        return Theme::partial('shortcodes.follow-us.index', compact('shortcode', 'variableStyles'));
    });

    Shortcode::setPreviewImage('follow-us', Theme::asset()->url('images/ui-blocks/follow-us.png'));

    Shortcode::setAdminConfig('follow-us', function (array $attributes) {
        $fields = [
            'title' => [
                'type' => 'text',
                'title' => __('Title'),
            ],
            'icon' => [
                'type' => 'coreIcon',
                'title' => __('Icon'),
            ],
            'url' => [
                'type' => 'text',
                'title' => __('URL'),
            ],
        ];

        return ShortcodeForm::createFromArray($attributes)
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Title'))
                    ->toArray()
            )
            ->add(
                'subtitle',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Subtitle'))
                    ->toArray()
            )
            ->add(
                'text_color',
                ColorField::class,
                InputFieldOption::make()
                    ->label(__('Text color'))
                    ->toArray()
            )
            ->add(
                'background_color',
                ColorField::class,
                InputFieldOption::make()
                    ->label(__('Background color'))
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
                'tabs',
                ShortcodeTabsField::class,
                ShortcodeTabsFieldOption::make()
                    ->fields($fields)
                    ->attrs($attributes)
                    ->toArray()
            );
    });
});
