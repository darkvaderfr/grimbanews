<?php

use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\RepeaterField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;
use Illuminate\Support\Arr;

class FollowUsWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Follow Us'),
            'description' => __('Follow Us Widget'),
            'items' => [],
            'title' => __('Follow Us'),
        ]);
    }

    public function settingForm(): WidgetForm|string|null
    {
        $fields = [
            [
                'type' => 'text',
                'label' => __('Title'),
                'label_attr' => ['class' => 'form-label required'],
                'attributes' => [
                    'name' => 'title',
                    'value' => null,
                    'options' => [
                        'class' => 'form-control',
                    ],
                ],
            ],
            [
                'type' => 'coreIcon',
                'label' => __('Icon'),
                'label_attr' => ['class' => 'form-label required'],
                'attributes' => [
                    'name' => 'icon',
                    'value' => null,
                    'options' => [
                        'class' => 'form-control',
                    ],
                ],
            ],
            [
                'type' => 'text',
                'label' => __('URL'),
                'label_attr' => ['class' => 'form-label'],
                'attributes' => [
                    'name' => 'url',
                    'value' => null,
                    'options' => [
                        'class' => 'form-control',
                    ],
                ],
            ],
        ];

        return WidgetForm::createFromArray($this->getConfig())
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label('Title')
                    ->toArray()
            )
            ->add('items', RepeaterField::class, [
                'fields' => $fields,
                'label' => __('Items'),
            ]);
    }

    public function data(): array
    {
        $items = Arr::get($this->getConfig(), 'items');

        if (is_string($items)) {
            $items = [];
        }

        return compact('items');
    }
}
