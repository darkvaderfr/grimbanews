<?php

use Botble\Base\Forms\FieldOptions\AlertFieldOption;
use Botble\Base\Forms\Fields\AlertField;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;

class SocialLinksWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Social Links'),
            'description' => __('Widget display social links network'),
            'items' => [],
        ]);
    }

    protected function settingForm(): WidgetForm|string|null
    {
        return WidgetForm::createFromArray($this->getConfig())
            ->add(
                'alert',
                AlertField::class,
                AlertFieldOption::make()
                    ->content(__('You can edit your social links in the Appearance → Theme Options → Social Links.'))
                    ->toArray()
            );
    }
}
