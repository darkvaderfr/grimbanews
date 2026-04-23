@php
    $bgImage = Theme::get('breadcrumb_background_image') ?: theme_option('breadcrumb_background_image');
    $bgColor = Theme::get('breadcrumb_background_color') ?: theme_option('breadcrumb_background_color');
    $textColor = Theme::get('breadcrumb_text_color') ?: theme_option('breadcrumb_text_color');
    $hasTextColor = (! $textColor) || $textColor !== 'transparent';
@endphp

@if (($pageTitle = Theme::get('pageTitle')) || Theme::get('isDetailPage', false))
    <div class="echo-breadcrumb-area"
        @style([
            sprintf('background-image: url(%s)', RvMedia::getImageUrl($bgImage)) => $bgImage,
            '--breadcrumb-bg-color: ' . $bgColor => $bgColor,
            '--breadcrumb-txt-color:' . $textColor => $hasTextColor
        ])
    >
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div @class(['breadcrumb-inner text-center', 'echo-breadcrumb-text-custom' => $hasTextColor])>
                        <div class="meta">
                            @foreach (Theme::breadcrumb()->getCrumbs() as $crumb)
                                @if (! $loop->last)
                                    <a href="{{ $crumb['url'] }}" class="next">{{ $crumb['label'] }}</a>
                                    <span class="ms-2 me-2">/</span>
                                @else
                                    <span class="next">{{ $crumb['label'] }}</span>
                                @endif
                            @endforeach
                        </div>

                        @if ($pageTitle)
                            <h1 class="title">{!! BaseHelper::clean($pageTitle) !!}</h1>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
