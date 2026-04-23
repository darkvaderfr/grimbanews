@php
    SeoHelper::setTitle(__('404 - Not found'));
    Theme::fireEventGlobalAssets();
@endphp
@extends(Theme::getThemeNamespace('layouts.base'))

@section('content')
    <div class="echo-404-page">
        <section class="echo-404-area">
            <div class="echo-container">
                <div class="echo-error-content">
                    <div class="echo-error">
                        <div class="echo-error-heading">
                            <h1>404</h1>
                        </div>
                        <div class="echo-error-sub-heading">
                            <h3>{{ __('Page not found') }}</h3>
                        </div>
                        <div class="echo-error-pera">
                            <p>{!! BaseHelper::clean(__('Sorry, the page you seems looking for,<br> has been moved, redirected or moved permanently.')) !!}</p>
                        </div>
                        <div class="error-btn">
                            <a href="{{ BaseHelper::getHomepageUrl() }}" class="text-capitalize">{{ __('Go back home') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection


