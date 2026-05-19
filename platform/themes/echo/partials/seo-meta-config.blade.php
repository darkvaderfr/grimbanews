{{-- Wave KKKKKK (Vader 2026-05-19) — single source of truth for the
     SEO meta setup that BOTH grimba-home and grimba-chrome layouts
     used to duplicate. Runs BEFORE Theme::header() so Botble's
     SeoHelper has a chance to merge our overrides into its own
     emission. Reads `grimba_og_image` from Theme::set() (post.blade
     sets it per article) and falls back to /og/home.png.

     Emits, via SeoHelper:
       - og:image (= Theme::get('grimba_og_image') ?: /og/home.png)
       - og:image:width / og:image:height (paired adjacent to og:image)
       - twitter:card type
       - og:locale + og:locale:alternate (FR ↔ EN swap)
       - og:type=website on home (Botble blog plugin defaults to article)

     Sets a Theme::set('__grimba_og_image_resolved') flag so the
     partials.seo-meta-twitter-image partial can emit twitter:image
     against the same URL without re-resolving.

     Layouts pass `is_home => true` if they're the home-layout entry
     (so we know to override og:type to 'website'). --}}
@php
    $__isHomeLayout = (bool) ($is_home ?? false);
    $__grimbaOgImageResolved = Theme::get('grimba_og_image') ?: url('/og/home.png');
    Theme::set('__grimba_og_image_resolved', $__grimbaOgImageResolved);

    \Botble\SeoHelper\Facades\SeoHelper::setImage($__grimbaOgImageResolved);
    \Botble\SeoHelper\Facades\SeoHelper::openGraph()->addProperty('image:width', '1200');
    \Botble\SeoHelper\Facades\SeoHelper::openGraph()->addProperty('image:height', '630');
    \Botble\SeoHelper\Facades\SeoHelper::twitter()->setType('summary_large_image');

    // Wave YYYYYY — og:site_name should be just the brand, not the
    // full title+tagline. Botble defaulted to the page title which
    // bloats Facebook/LinkedIn unfurls ("Grimba News — Voyez chaque
    // angle de chaque histoire" instead of "GrimbaNews"). The brand
    // name is "GrimbaNews" per Iboga's brand guide. setSiteName
    // overrides the OG-spec og:site_name value.
    \Botble\SeoHelper\Facades\SeoHelper::openGraph()->setSiteName('GrimbaNews');

    if ($__isHomeLayout) {
        \Botble\SeoHelper\Facades\SeoHelper::openGraph()->setType('website');
    }

    $__grimbaCurLocale = app()->getLocale();
    $__grimbaOgLocale = $__grimbaCurLocale === 'en' ? 'en_US' : 'fr_FR';
    $__grimbaOgLocaleAlt = $__grimbaCurLocale === 'en' ? 'fr_FR' : 'en_US';
    \Botble\SeoHelper\Facades\SeoHelper::openGraph()->addProperty('locale', $__grimbaOgLocale);
    \Botble\SeoHelper\Facades\SeoHelper::openGraph()->addProperty('locale:alternate', $__grimbaOgLocaleAlt);

    // Wave RRRRRR (Vader 2026-05-19) — canonical URL. Botble's SeoHelper
    // only emits rel=canonical when SeoHelper::meta()->setUrl() has been
    // called. Post pages get it from the blog plugin; custom routes
    // (/breaking, /latest, /comparatif/{id}, /sources, /advertise, etc.)
    // didn't, so they shipped without canonical — Google relies on it.
    // Always set to the current path (query stripped) — overwriting the
    // blog plugin's per-post canonical is a no-op since $post->url
    // resolves to the same path.
    //
    // Wave WWWWWWW (Vader 2026-05-19) — exception: 404 pages must NOT
    // ship a canonical pointing at the broken URL. Search engines
    // interpret rel=canonical on a 404 as a contradictory signal
    // ("this URL is canonical to itself, but also doesn't exist").
    // The 404 view sets Theme::set('grimba_is_404', true); skip
    // canonical emission when that flag is set.
    $__grimbaIs404 = (bool) Theme::get('grimba_is_404');
    if (! $__grimbaIs404) {
        \Botble\SeoHelper\Facades\SeoHelper::meta()->setUrl(url()->current());
    }

    // Wave TTTTTT + IIIIIII (Vader 2026-05-19) — robots meta. Botble's
    // blog plugin auto-emits "index, follow" on post listings but not
    // on our custom routes. Without an explicit meta, crawlers default
    // to "index, follow" anyway — but being explicit signals intent.
    //
    // noindex on personalized / duplicate-content surfaces:
    //   - /search?q=... — duplicate content of underlying articles
    //   - /coffre — saved-article vault (per-cookie, empty for crawlers)
    //   - /coffre-share — shared-vault URLs (one-off per recipient)
    //   - /account — auth surface
    //   - /pour-vous — personalized feed (per-cookie history)
    //   - /local — geo-personalized (per-IP city detection)
    //   - 404 errors — Wave WWWWWWW; broken URLs shouldn't be indexed
    // Wave LLLLLLL — the previous noindex predicate looked for 'for-you'
    // but that route doesn't exist; FR canonical is /pour-vous. Update
    // to match the actual path so the personalized feed actually gets
    // tagged noindex.
    $__grimbaPath = request()->path();
    $__grimbaNoindex = $__grimbaIs404
        || str_starts_with($__grimbaPath, 'search')
        || str_starts_with($__grimbaPath, 'coffre')
        || str_starts_with($__grimbaPath, 'account')
        || str_starts_with($__grimbaPath, 'pour-vous')
        || str_starts_with($__grimbaPath, 'local');
    \Botble\SeoHelper\Facades\SeoHelper::meta()->addMeta(
        'robots',
        $__grimbaNoindex ? 'noindex, follow' : 'index, follow'
    );
@endphp
