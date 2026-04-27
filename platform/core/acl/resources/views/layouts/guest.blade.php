<!doctype html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-bs-theme="dark"
>

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

    {!! BaseHelper::googleFonts('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700;9..144,800&family=Public+Sans:wght@400;500;600;700&display=swap') !!}

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

        .grimba-auth-minimal {
            min-height: 100vh;
            overflow-x: hidden;
            background:
                radial-gradient(circle at 18% 22%, rgba(224, 122, 112, 0.18), transparent 28rem),
                radial-gradient(circle at 86% 76%, rgba(220, 200, 160, 0.16), transparent 30rem),
                #0f0d08;
        }

        .grimba-auth-minimal #app {
            min-height: 100vh;
            isolation: isolate;
        }

        .grimba-auth-minimal main {
            min-height: 100vh;
        }

        .grimba-auth-pane {
            position: relative;
            background:
                linear-gradient(135deg, rgba(15, 13, 8, 0.96), rgba(26, 23, 19, 0.92)),
                radial-gradient(circle at 50% 18%, rgba(224, 122, 112, 0.12), transparent 22rem);
            box-shadow: inset -1px 0 rgba(246, 241, 232, 0.08);
        }

        .grimba-auth-pane::before {
            position: absolute;
            inset: 2rem;
            z-index: -1;
            content: "";
            border: 1px solid rgba(246, 241, 232, 0.08);
            border-radius: 2rem;
            background: rgba(246, 241, 232, 0.035);
            filter: blur(0);
        }

        .grimba-auth-card {
            width: min(100%, 34rem);
            margin-inline: auto;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            border: 1px solid rgba(246, 241, 232, 0.11);
            border-radius: 2rem;
            background: rgba(246, 241, 232, 0.075);
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.34);
            backdrop-filter: blur(18px) saturate(1.2);
            -webkit-backdrop-filter: blur(18px) saturate(1.2);
        }

        .grimba-auth-wordmark {
            display: inline-flex;
            align-items: baseline;
            justify-content: center;
            gap: 0;
            color: #f6f1e8;
            font-family: "Fraunces", "Playfair Display", Georgia, serif;
            font-size: clamp(2.2rem, 4.5vw, 3rem);
            font-weight: 800;
            letter-spacing: -0.045em;
            line-height: 1;
            text-decoration: none;
            text-shadow: 0 16px 42px rgba(0, 0, 0, 0.42);
        }

        .grimba-auth-wordmark:hover,
        .grimba-auth-wordmark:focus {
            color: #f6f1e8;
            text-decoration: none;
        }

        .grimba-auth-wordmark__accent {
            color: #e07a70;
            letter-spacing: -0.05em;
        }

        .grimba-auth-visual {
            overflow: hidden;
        }

        .grimba-auth-visual__image {
            position: absolute;
            inset: 0;
            background-position: center;
            background-size: cover;
            transform: scale(1.025);
            filter: saturate(1.06) contrast(1.02);
        }

        .grimba-auth-visual::before {
            position: absolute;
            inset: 0;
            z-index: 1;
            content: "";
            background:
                linear-gradient(90deg, rgba(15, 13, 8, 0.36), transparent 34%),
                radial-gradient(circle at 18% 16%, rgba(246, 241, 232, 0.38), transparent 18rem),
                radial-gradient(circle at 88% 82%, rgba(15, 13, 8, 0.34), transparent 26rem);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            pointer-events: none;
        }

        .grimba-auth-visual__caption {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            z-index: 2;
            max-width: 30rem;
            padding: 1.25rem 1.5rem;
            border: 1px solid rgba(246, 241, 232, 0.24);
            border-radius: 1.25rem;
            background: rgba(15, 13, 8, 0.34);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.28);
            transform: translateZ(0);
        }

        .grimba-auth-visual__caption::before {
            position: absolute;
            inset: 0;
            z-index: -1;
            content: "";
            background: rgba(15, 13, 8, 0.46);
            backdrop-filter: blur(14px) saturate(1.15);
            -webkit-backdrop-filter: blur(14px) saturate(1.15);
        }

        .grimba-auth-visual__caption h1,
        .grimba-auth-visual__caption p {
            position: relative;
            z-index: 1;
            filter: none;
        }

        .grimba-auth-minimal .form-control,
        .grimba-auth-minimal .form-check-input {
            background-color: rgba(246, 241, 232, 0.08) !important;
            border-color: rgba(246, 241, 232, 0.18) !important;
            color: #f6f1e8 !important;
        }

        .grimba-auth-minimal .form-control::placeholder {
            color: rgba(246, 241, 232, 0.58) !important;
        }

        .grimba-auth-minimal .form-label,
        .grimba-auth-minimal .form-check-label,
        .grimba-auth-minimal .form-label-description a {
            color: rgba(246, 241, 232, 0.82) !important;
        }

        .grimba-auth-minimal .btn-primary {
            background: #f6f1e8 !important;
            border-color: #f6f1e8 !important;
            color: #1a1713 !important;
        }

        @media (max-width: 991.98px) {
            .grimba-auth-card {
                width: min(100%, 36rem);
            }
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
>
    <div id="app">
        <main class="row g-0 flex-fill vh-100">
            <div class="grimba-auth-pane col-12 col-lg-6 col-xl-4 d-flex flex-column justify-content-center">
                <div class="container container-tight my-5 px-lg-5">
                    <div class="grimba-auth-card">
                        <div class="text-center mb-4">
                            <a
                                href="{{ route('dashboard.index') }}"
                                class="grimba-auth-wordmark"
                                aria-label="{{ $adminTitle }}"
                            >
                                <span>Grimba</span><span class="grimba-auth-wordmark__accent">News</span>
                            </a>
                        </div>

                        @yield('content')
                    </div>
                </div>
            </div>
            <div class="grimba-auth-visual position-relative col-12 col-lg-6 col-xl-8 d-none d-lg-block">
                <div
                    class="grimba-auth-visual__image"
                    style="background-image: url({{ $backgroundUrl }})"
                ></div>
                <div class="end-0 bottom-0 position-absolute">
                    <div class="grimba-auth-visual__caption text-white me-5 mb-4">
                        <h1 class="mb-1">{{ $adminTitle }}</h1>
                        <p>@include('core/base::partials.copyright')</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
