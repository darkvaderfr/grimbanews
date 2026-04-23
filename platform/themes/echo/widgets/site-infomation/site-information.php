<?php

use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\TextField;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;

class SiteInformationWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Site Information'),
            'description' => __('Add a site information to your widget area.'),
            'title' => null,
            'email' => null,
            'hotline' => null,
            'address' => null,
        ]);
    }

    protected function settingForm(): WidgetForm|string|null
    {
        return WidgetForm::createFromArray($this->getConfig())
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Title'))
                    ->placeholder(__('Enter title'))
                    ->toArray()
            )
            ->add(
                'hotline',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Hotline'))
                    ->placeholder(__('Enter hotline'))
                    ->defaultValue(theme_option('phone'))
                    ->toArray()
            )
            ->add(
                'email',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Email'))
                    ->placeholder(__('Enter email'))
                    ->defaultValue(theme_option('email'))
                    ->toArray()
            )
            ->add(
                'address',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Address'))
                    ->placeholder(__('Enter address'))
                    ->toArray()
            );
    }
}
