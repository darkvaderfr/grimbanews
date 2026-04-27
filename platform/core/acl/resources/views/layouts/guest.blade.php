<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    >
    <meta
        http-equiv="X-UA-Compatible"
        content="ie=edge"
    >
    <title>{{ PageTitle::getTitle() }}</title>

    @if ($csrfToken = csrf_token())
        <meta
            name="csrf-token"
            content="{{ $csrfToken }}"
        >
    @endif

    @php
        $faviconUrl = AdminHelper::getAdminFaviconUrl();
        $faviconType = setting('admin_favicon_type', 'image/x-icon');
        $adminTitle = setting('admin_title', config('core.base.general.base_name'));
        $copyright = strip_tags(trans('core/base::layouts.copyright', [
            'year' => Carbon\Carbon::now()->year,
            'company' => $adminTitle,
            'version' => get_cms_version(),
        ]));
    @endphp

    <link
        href="{{ $faviconUrl }}"
        rel="icon shortcut"
        type="{{ $faviconType }}"
    >
    <meta
        property="og:image"
        content="{{ $faviconUrl }}"
    >
    <meta
        name="description"
        content="{{ $copyright }}"
    >
    <meta
        property="og:description"
        content="{{ $copyright }}"
    >

    <style>
        [v-cloak],
        [x-cloak] {
            display: none;
        }

        :root {
            --primary-font: "{{ setting('admin_primary_font', 'Inter') }}";
            --primary-color: {{ $primaryColor = setting('admin_primary_color', '#206bc4') }};
            --primary-color-rgb: {{ implode(', ', BaseHelper::hexToRgb($primaryColor)) }};
            --secondary-color: {{ $secondaryColor = setting('admin_secondary_color', '#6c7a91') }};
            --secondary-color-rgb: {{ implode(', ', BaseHelper::hexToRgb($secondaryColor)) }};
            --heading-color: {{ setting('admin_heading_color', 'inherit') }};
            --text-color: {{ $textColor = setting('admin_text_color', '#182433') }};
            --text-color-rgb: {{ implode(', ', BaseHelper::hexToRgb($textColor)) }};
            --link-color: {{ $linkColor = setting('admin_link_color', '#206bc4') }};
            --link-color-rgb: {{ implode(', ', BaseHelper::hexToRgb($linkColor)) }};
            --link-hover-color: {{ $linkHoverColor = setting('admin_link_hover_color', '#206bc4') }};
            --link-hover-color-rgb: {{ implode(', ', BaseHelper::hexToRgb($linkHoverColor)) }};
        }
    </style>

    <link
        rel="stylesheet"
        href="{{ asset(BaseHelper::adminLanguageDirection() === 'rtl' ? 'vendor/core/core/base/css/core.rtl.css' : 'vendor/core/core/base/css/core.css') }}?v={{ get_cms_version() }}"
    >
    <link
        rel="stylesheet"
        href="{{ asset('themes/echo/css/grimba-admin.css') }}?v={{ get_cms_version() }}"
    >

    @yield('head')

    <script>
        window.siteUrl = "{{ url('') }}";
    </script>

    @stack('header')

    {!! AdminAppearance::getCustomCSS() !!}
    {!! apply_filters(BASE_FILTER_HEAD_LAYOUT_TEMPLATE, null) !!}
</head>

<body
    class="@yield('body-class', 'page-sidebar-closed-hide-logo page-content-white page-container-bg-solid grimba-auth-minimal')"
    style="@yield('body-style')"
    @if (BaseHelper::adminLanguageDirection() === 'rtl') dir="rtl" @endif
    data-bs-theme="dark"
>
    <div id="app">
        <main class="row g-0 flex-fill vh-100">
            <div class="col-12 col-lg-6 col-xl-4 border-top-wide border-primary d-flex flex-column justify-content-center">
                <div class="container container-tight my-5 px-lg-5">
                    <div class="text-center mb-4">
                        @include('core/base::partials.logo', ['defaultLogoHeight' => 50])
                    </div>

                    @yield('content')
                </div>
            </div>
            <div class="position-relative col-12 col-lg-6 col-xl-8 d-none d-lg-block">
                <div
                    class="bg-cover bg-white h-100 min-vh-100"
                    style="background-image: url({{ $backgroundUrl }})"
                ></div>
                <div class="end-0 bottom-0 position-absolute">
                    <div class="text-white me-5 mb-4">
                        <h1 class="mb-1">{{ $adminTitle }}</h1>
                        <p>@include('core/base::partials.copyright')</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
