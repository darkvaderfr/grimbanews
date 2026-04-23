<?php

use ArchiElite\Announcement\Models\Announcement;
use Botble\Base\Facades\BaseHelper;
use Botble\Shortcode\View\View;
use Botble\Theme\Theme;

return [

    /*
    |--------------------------------------------------------------------------
    | Inherit from another theme
    |--------------------------------------------------------------------------
    |
    | Set up inherit from another if the file is not exists,
    | this is work with "layouts", "partials" and "views"
    |
    | [Notice] assets cannot inherit.
    |
    */

    'inherit' => null, //default

    /*
    |--------------------------------------------------------------------------
    | Listener from events
    |--------------------------------------------------------------------------
    |
    | You can hook a theme when event fired on activities
    | this is cool feature to set up a title, meta, default styles and scripts.
    |
    | [Notice] these events can be overridden by package config.
    |
    */

    'events' => [

        // Before event inherit from package config and the theme that call before,
        // you can use this event to set meta, breadcrumb template or anything
        // you want inheriting.
        'before' => function ($theme): void {
            // You can remove this line anytime.
        },

        // Listen on event before render a theme,
        // this event should call to assign some assets,
        // breadcrumb template.
        'beforeRenderTheme' => function (Theme $theme): void {
            // Partial composer.
            // $theme->partialComposer('header', function($view) {
            //     $view->with('auth', \Auth::user());
            // });

            // You may use this event to set up your assets.

            if (BaseHelper::isRtlEnabled()) {
                $theme->asset()->usePath()->add('bootstrap', 'plugins/bootstrap/bootstrap.rtl.min.css');
            } else {
                $theme->asset()->usePath()->add('bootstrap', 'plugins/bootstrap/bootstrap.min.css');
            }

            $theme->asset()->usePath()->add('magnific-popup-css', 'plugins/magnific-popup/magnific-popup.css');
            $theme->asset()->usePath()->add('magnify-popup-css', 'plugins/magnify-popup/magnify-popup.css');
            $theme->asset()->usePath()->add('metismenu-css', 'plugins/metismenu/metismenu.css');
            $theme->asset()->usePath()->add('swiper-css', 'plugins/swiper/swiper.css');
            $theme->asset()->usePath()->add('fontawesome-css', 'css/fontawesome-5.css');
            $theme->asset()->usePath()->add('style', 'css/style.css');

            $theme->asset()->container('footer')->usePath()->add('jquery', 'plugins/jquery.min.js');
            $theme->asset()->container('footer')->usePath()->add('bootstrap-js', 'plugins/bootstrap/bootstrap.bundle.min.js');
            $theme->asset()->container('footer')->usePath()->add('magnific-popup-js', 'plugins/magnific-popup/magnific-popup.js');
            $theme->asset()->container('footer')->usePath()->add('magnify-popup-js', 'plugins/magnify-popup/magnify-popup.js');
            $theme->asset()->container('footer')->usePath()->add('metismenu-js', 'plugins/metismenu/metisMenu.min.js');
            $theme->asset()->container('footer')->usePath()->add('swiper-js', 'plugins/swiper/swiper.js');
            $theme->asset()->container('footer')->usePath()->add('counter-up-js', 'plugins/counter-up.js');
            $theme->asset()->container('footer')->usePath()->add('isotop-js', 'plugins/isotop.js');
            $theme->asset()->container('footer')->usePath()->add('resize-sensor-js', 'plugins/resize-sensor.min.js');
            $theme->asset()->container('footer')->usePath()->add('theia-sticky-sidebar-js', 'plugins/theia-sticky-sidebar.min.js');
            $theme->asset()->container('footer')->usePath()->add('audio-js', 'plugins/audio.js');

            $theme->asset()->container('footer')->usePath()->add('script-js', 'js/script.js');
            $theme->asset()->container('footer')->usePath()->add('main-js', 'js/main.js');

            if (function_exists('shortcode')) {
                $theme->composer(['page', 'post'], function (View $view): void {
                    $view->withShortcodes();
                });
            }

            $theme->addBodyAttributes(['class' => 'home-one']);

            if (is_plugin_active('announcement') && Announcement::query()->available()->exists()) {
                $theme->addBodyAttributes(['data-bb-toggle' => 'announcement',  'data-bb-target' => 'header']);
            }
        },

        // Listen on event before render a layout,
        // this should call to assign style, script for a layout.
        'beforeRenderLayout' => [
            //
        ],
    ],
];
