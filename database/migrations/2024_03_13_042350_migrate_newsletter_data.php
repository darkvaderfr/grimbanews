<?php

use Botble\Setting\Facades\Setting;
use Botble\Theme\Facades\ThemeOption;
use Botble\Widget\Models\Widget;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;

return new class () extends Migration {
    public function up(): void
    {
        $widget = Widget::query()
            ->where('widget_id', 'ActionButtonsWidget')
            ->first();

        if (! $widget) {
            return;
        }

        $data = $widget->data;

        try {
            Setting::set(ThemeOption::prepareFromArray([
                'newsletter_popup_enable' => true,
                'newsletter_popup_image' => (string)Arr::get($data, 'form_newsletter_image'),
                'newsletter_popup_title' => (string)Arr::get($data, 'form_newsletter_title'),
                'newsletter_popup_subtitle' => (string)Arr::get($data, 'form_newsletter_subtitle'),
                'newsletter_popup_description' => (string)Arr::get($data, 'form_newsletter_description'),
            ]));

            Setting::save();
        } catch (Throwable $e) {
            return;
        }
    }
};
