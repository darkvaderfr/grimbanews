@if ($content = $shortcode->text)
    <p class="echo-hero-discription">
        <span class="text-dropped-cap">{{ substr($content, 0, 1) ?? null }}</span>
        {{ substr($content, 1) ?? null }}
    </p>
    <div class="clearfix"></div>
@endif
