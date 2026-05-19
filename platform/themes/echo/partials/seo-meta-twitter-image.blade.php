{{-- Wave KKKKKK — twitter:image emitted manually AFTER Theme::header()
     so SeoHelper's singleton-state Card::addImage() can't accumulate
     across requests (would emit numbered twitter:image{0}+{1} variants
     Twitter doesn't honor). The URL was resolved in partials.seo-meta-config
     and stashed in Theme::set('__grimba_og_image_resolved'). --}}
<meta name="twitter:image" content="{{ Theme::get('__grimba_og_image_resolved') ?: url('/og/home.png') }}">
