<div class="echo-hero-section inner-post-3 pb-0">
    <div class="echo-post-style-quote-info-flexing">
        <div class="echo-quote-area">
            @if ($image = $shortcode->top_left_image)
                {{ RvMedia::image($image, 'quote') }}
            @endif

            @if ($quote = $shortcode->quote)
                <h2 class="quote-title">{!! BaseHelper::clean($quote) !!}</h2>
            @endif
            @if ($author = $shortcode->author)
                <p class="author">{{ $author }}</p>
            @endif
        </div>

        @if ($description = $shortcode->description)
            <div class="echo-quote-info d-flex align-items-center">
                <p>{!! BaseHelper::clean($description) !!}</p>
            </div>
        @endif
    </div>
</div>
