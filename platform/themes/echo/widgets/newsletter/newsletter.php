<?php

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\ButtonFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Newsletter\Forms\Fronts\NewsletterForm;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;
use Illuminate\Support\Arr;

class NewsletterWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Newsletter'),
            'description' => __('Add a newsletter to your widget area.'),
            'title' => null,
            'subtitle' => null,
            'image' => null,
            'position' => 'footer',
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
                'image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Image'))
                    ->helperText(__('This image does not be used in the footer widget'))
                    ->toArray()
            );

    }

    public function data(): array
    {
        if (! is_plugin_active('newsletter')) {
            return [];
        }

        $form = NewsletterForm::create();

        switch ($this->group->getId()) {
            case 'footer_sidebar':
                $form
                    ->addBefore(
                        'email',
                        'open_wrapper',
                        HtmlField::class,
                        HtmlFieldOption::make()
                            ->content('<div class="echo-subscribe-input-fill">')
                            ->toArray()
                    )
                    ->addAfter(
                        'email',
                        'close_wrapper',
                        HtmlField::class,
                        HtmlFieldOption::make()
                            ->content('<i class="fa-regular fa-envelope newsletter-icon-without-captcha"></i></div>')
                            ->toArray()
                    );

                break;

            case 'menu_sidebar':
                $form
                    ->addBefore(
                        'email',
                        'open_wrapper',
                        HtmlField::class,
                        HtmlFieldOption::make()
                            ->content('<div class="input-div">')
                            ->toArray()
                    )
                    ->addAfter(
                        'email',
                        'close_wrapper',
                        HtmlField::class,
                        HtmlFieldOption::make()
                            ->content('</div>')
                            ->toArray()
                    );

                break;

            case 'above_footer_sidebar':
                $form
                    ->addBefore(
                        'email',
                        'open_wrapper',
                        HtmlField::class,
                        HtmlFieldOption::make()
                            ->content('<div class="echo-subscribe-input-fill"><svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.8" d="M14.4414 11.6674C14.4402 11.8345 14.3734 11.9944 14.2553 12.1127C14.1371 12.2309 13.9773 12.2979 13.8101 12.2993H2.34541C2.17792 12.2991 2.01736 12.2325 1.89899 12.114C1.78062 11.9955 1.71413 11.8348 1.71413 11.6674V11.0265H13.1687V3.58109L8.07777 8.16291L1.71413 2.43564V1.48109C1.71413 1.31232 1.78118 1.15045 1.90052 1.03111C2.01986 0.911772 2.18172 0.844727 2.3505 0.844727H13.805C13.9738 0.844727 14.1357 0.911772 14.255 1.03111C14.3744 1.15045 14.4414 1.31232 14.4414 1.48109V11.6674ZM3.26304 2.11745L8.07777 6.45109L12.8925 2.11745H3.26304ZM0.441406 8.48109H5.53232V9.75382H0.441406V8.48109ZM0.441406 5.29927H3.62322V6.572H0.441406V5.29927Z" fill="white"></path></svg>')
                            ->toArray()
                    );

                $descriptionHtml = '';

                if ($description = Arr::get($this->getConfig(), 'description')) {
                    $descriptionHtml = sprintf('<p class="desc">%s</p>', BaseHelper::clean($description));
                }

                $form->addAfter(
                    'email',
                    'close_wrapper',
                    HtmlField::class,
                    HtmlFieldOption::make()
                            ->content($descriptionHtml . '</div>')
                            ->toArray()
                )
                    ->remove('submit')
                    ->add(
                        'submit',
                        'submit',
                        ButtonFieldOption::make()
                            ->label(__('Subscribe'))
                            ->cssClass('btn btn-primary echo-footer-area-subscribe-button')
                            ->toArray(),
                    );
        }

        return compact('form');
    }
}
