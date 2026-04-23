@php
    $backgroundColor = $shortcode->background_color;
    $backgroundImage = $shortcode->background_image ? RvMedia::url($shortcode->background_image) : null;
@endphp

<section class="echo-software-download-area home-four">
    {!! Theme::partial('shortcodes.app-downloads.content', compact('shortcode')) !!}
</section>
