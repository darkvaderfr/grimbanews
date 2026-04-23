<?php

use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormCollapse;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;
use Illuminate\Support\Arr;

class ActionButtonsWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Action Buttons'),
            'description' => __('Action Buttons Widget'),
            'enable_dark_light_switcher_button' => true,
            'enable_search_button' => true,
            'enable_toggle_side_menu_button' => true,
            'alignment' => 'end',
        ]);
    }

    public function settingForm(): WidgetForm|string|null
    {
        return WidgetForm::createFromArray($this->getConfig())
            ->add(
                'enable_dark_light_switcher_button',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(__('Enable Dark/Light Switcher Button?'))
                    ->defaultValue(true)
                    ->toArray()
            )
            ->add(
                'enable_search_button',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(__('Enable Search Button?'))
                    ->toArray()
            )
            ->add(
                'enable_toggle_side_menu_button',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(__('Enable Toggle Side Menu Button?'))
                    ->toArray()
            )
            ->when(is_plugin_active('newsletter'), function (WidgetForm $form): void {
                $form->addCollapsible(
                    FormCollapse::make('newsletter-form-config')
                        ->targetField(
                            'enable_newsletter_button',
                            OnOffField::class,
                            OnOffFieldOption::make()
                                ->label(__('Enable Newsletter Button?'))
                                ->when(! theme_option('newsletter_popup_enable', false), function (OnOffFieldOption $option) {
                                    return $option
                                        ->disabled()
                                        ->helperText(__('You need to setup Newsletter Popup in Theme Options -> Newsletter Popup to use this feature.'));
                                })
                                ->toArray()
                        )
                        ->fieldset(function (WidgetForm $form): void {
                            $form->add(
                                'form_newsletter_button_label',
                                TextField::class,
                                TextFieldOption::make()
                                    ->label(__('Button Label'))
                                    ->toArray()
                            );
                        })
                        ->isOpened(Arr::get($this->getConfig(), 'enable_newsletter_button', false))
                );
            })
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
            );
    }
}
