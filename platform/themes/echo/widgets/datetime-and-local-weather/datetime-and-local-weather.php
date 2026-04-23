<?php

use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;
use Illuminate\Support\Collection;

class DateTimeAndLocalWeather extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Datetime & Local Weather'),
            'description' => __('Datetime & Local Weather Widget'),
            'alignment' => 'center',
        ]);
    }

    public function data(): array|Collection
    {
        return [
            ...parent::data(),
            'alignment' => $this->getConfig('alignment', 'center'),
        ];
    }

    protected function settingForm(): WidgetForm|string|null
    {
        return WidgetForm::createFromArray($this->getConfig())
            ->add(
                'enable_datetime',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(__('Enabled datetime?'))
                    ->defaultValue(1)
                    ->toArray()
            )
            ->add(
                'enable_weather',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(__('Enabled weather?'))
                    ->defaultValue(1)
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
            );
    }
}
