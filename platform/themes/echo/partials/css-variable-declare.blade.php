<style>
    :root {
        --primary-color: {{ $primaryColor = theme_option('primary_color', '#537FE7') }} !important;
        --secondary-color: {{ theme_option('secondary_color', 'inerit') }} !important;
        --tertiary-color: {{ theme_option('tertiary_color', 'inerit') }} !important;
        --header-text-color: {{ theme_option('header_text_color', '#ffffff') }} !important;
        --color-primary: {{ $primaryColor }} !important;
        --color-heading: {{ theme_option('heading_color', '#181823') }} !important;
        --text-color: {{ theme_option('text_color', '#5E5E5E') }} !important;
        --footer-background-color: {{ theme_option('footer_background_color', '#181823') }} !important;
        --footer-heading-color: {{ theme_option('footer_heading_color', '#FFF') }} !important;
        --footer-text-color: {{ theme_option('footer_text_color', '#FFF') }} !important;
        --primary-font: '{{ theme_option('primary_font', 'Inter') }}', sans-serif;
        --heading-font: '{{ theme_option('heading_font', 'Inter') }}', sans-serif;
        --primary-heading: '{{ theme_option('heading_font', 'Bona Nova') }}', sans-serif;
        --gradient-color: {{ sprintf('linear-gradient(126deg, %s, %s)', theme_option('gradient_from', '#9F68F5') . ' 14.85%', theme_option('gradient_to', '#BEE44F') . ' 86.39%') }} !important;
    }
</style>
