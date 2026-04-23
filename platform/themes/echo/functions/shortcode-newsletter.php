<?php

use Botble\Base\Forms\FieldOptions\ButtonFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\ColorField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormFieldOptions;
use Botble\Media\Facades\RvMedia;
use Botble\Newsletter\Forms\Fronts\NewsletterForm;
use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Theme\Facades\Theme;
use Illuminate\Routing\Events\RouteMatched;

app('events')->listen(RouteMatched::class, function (): void {
    if (! is_plugin_active('newsletter')) {
        return;
    }

    Shortcode::register('newsletter', __('Newsletter'), __('Newsletter'), function (ShortcodeCompiler $shortcode): ?string {
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

        $form = NewsletterForm::create();

        $form
            ->addBefore(
                'email',
                'open_wrapper',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<div class="echo-subscribe-input-fill"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="21" viewBox="0 0 24 21" fill="none"><path d="M1.90625 0.493164H22.1562C22.4546 0.493164 22.7408 0.610227 22.9517 0.818601C23.1627 1.02697 23.2812 1.30959 23.2812 1.60428V19.3821C23.2812 19.6767 23.1627 19.9594 22.9517 20.1677C22.7408 20.3761 22.4546 20.4932 22.1562 20.4932H1.90625C1.60788 20.4932 1.32173 20.3761 1.11075 20.1677C0.899776 19.9594 0.78125 19.6767 0.78125 19.3821V1.60428C0.78125 1.30959 0.899776 1.02697 1.11075 0.818601C1.32173 0.610227 1.60788 0.493164 1.90625 0.493164ZM21.0312 5.20205L12.1122 13.0909L3.03125 5.17761V18.2709H21.0312V5.20205ZM3.60613 2.71539L12.0999 10.1176L20.471 2.71539H3.60613Z" fill="#E2E2E2"></path></svg>')
                    ->toArray()
            )
            ->addAfter(
                'email',
                'close_wrapper',
                HtmlField::class,
                HtmlFieldOption::make()
                ->content('</div>')
                ->toArray()
            )
            ->remove('submit')
            ->add(
                'submit',
                'submit',
                ButtonFieldOption::make()
                    ->label($shortcode->button_label ?: __('Subscribe'))
                    ->cssClass('btn btn-primary echo-footer-area-subscribe-button')
                    ->toArray(),
            );

        return Theme::partial('shortcodes.newsletter.index', compact('shortcode', 'variableStyles', 'form'));
    });

    Shortcode::setPreviewImage('newsletter', Theme::asset()->url('images/ui-blocks/newsletter.png'));

    Shortcode::setAdminConfig('newsletter', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Title'))
                    ->placeholder(__('Enter title'))
                    ->toArray()
            )
            ->add(
                'subtitle',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Subtitle'))
                    ->placeholder(__('Enter subtitle'))
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
            );
    });
});
