<div class="echo-quote-area">
    @if ($quote = $shortcode->quote)
        <h2 class="quote-title">{!! BaseHelper::clean($quote) !!}</h2>
    @endif

    @if ($author = $shortcode->author)
        <p class="author">{{ $author }}</p>
    @endif
</div>
