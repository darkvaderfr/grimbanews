@php
    $footerBackgroundColor = theme_option('footer_background_color', '#181823');
    $footerBackgroundImage = theme_option('footer_background_image') ? RvMedia::url(theme_option('footer_background_image')) : null;
@endphp

<footer class="echo-footer-area" id="footer"
    @style([
        "background-color: $footerBackgroundColor !important;" => $footerBackgroundColor,
        "--background-image: url('$footerBackgroundImage')" => $footerBackgroundImage,
    ])
>
    {!! Theme::partial('footer.content') !!}
</footer>
