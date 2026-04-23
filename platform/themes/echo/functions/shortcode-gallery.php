<?php

use Botble\Base\Forms\FieldOptions\InputFieldOption;
use Botble\Base\Forms\Fields\ColorField;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Theme\Facades\Theme;
use Illuminate\Routing\Events\RouteMatched;

app('events')->listen(RouteMatched::class, function (): void {
    if (! is_plugin_active('gallery')) {
        return;
    }

    add_filter('galleries_box_template_view', function () {
        return Theme::getThemeNamespace('partials.shortcodes.galleries.index');
    });

    Shortcode::setPreviewImage('gallery', Theme::asset()->url('images/ui-blocks/gallery.png'));

    Shortcode::modifyAdminConfig('gallery', function (ShortcodeForm $form) {
        return $form->addAfter(
            'limit',
            'text_color',
            ColorField::class,
            InputFieldOption::make()
                ->label(__('Text color'))
                ->defaultValue('#fff')
                ->toArray(),
        )
            ->add(
                'background_color',
                ColorField::class,
                InputFieldOption::make()
                    ->label(__('Background color'))
                    ->defaultValue('#181823')
                    ->toArray()
            );
    });
});
