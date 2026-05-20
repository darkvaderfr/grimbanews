@php
    /*
     * S-LSAT-05 (Vader 2026-05-18) — tail expander.
     *
     * Sits at the bottom of every strict-locale-filtered surface
     * (home, /breaking, /latest, /dossiers). When the reader is on
     * EN, we count posts that exist natively in FR (and vice versa)
     * and offer a one-click toggle.
     *
     * Without this, a curious reader on `/breaking?lang=en` has no
     * idea there are 14 French posts they'd see by flipping. The
     * ribbon turns the strict filter from a wall into a door.
     *
     * Vars:
     *   $surface (string|null) — 'home' | 'breaking' | 'latest' |
     *     'dossiers' — used only for an analytics breadcrumb.
     *   $hours (int|null) — recency window for the count (default
     *     72h; tighter on /breaking).
     */

    use App\Support\GrimbaHomeFeed;
    use App\Support\GrimbaLanguageSettings;
    use App\Support\GrimbaTranslationPresenter;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    if (! GrimbaLanguageSettings::tailExpanderEnabled()) {
        return;
    }

    $surface = $surface ?? 'unknown';
    $hours = max(1, min(168, (int) ($hours ?? 72)));

    $readerLocale = GrimbaHomeFeed::resolveReaderLocale();
    $opposite = $readerLocale === 'fr' ? 'en' : 'fr';
    $oppositeLabel = $opposite === 'fr' ? 'français' : 'English';

    // Count posts that natively match the opposite locale AND don't
    // have a translation in the reader's locale yet. Cache per
    // surface/locale/window/region for 60s — the count moves slowly
    // and the home rail hits this on every page load.
    $region = method_exists(GrimbaHomeFeed::class, 'resolveRegionKey')
        ? null
        : 'all';
    $cacheKey = sprintf(
        'grimba_tail_expander:%s:%s:%dh',
        $surface,
        $readerLocale,
        $hours,
    );

    // Wave SSSSSSSS (Vader 2026-05-20) — graceful degradation when
    // the cache writer can't write (e.g. file-cache dir owned by a
    // prior root user, disk full, permission revoked). Without this
    // try/catch, a single bad cache file took the whole /dossiers
    // page to 500. Caller surfaces are display-only; if we can't
    // cache, we compute fresh.
    $__tailComputeFresh = function () use ($opposite, $readerLocale, $hours) {
        $query = DB::table('posts')
            ->where('status', 'published')
            ->where('original_language', $opposite)
            ->where('published_at', '>=', now()->subHours($hours));

        // Don't include posts that already have a reader-locale
        // translation — they're already in the surface.
        $query->where(function ($q) use ($readerLocale) {
            $q->where(function ($w) use ($readerLocale) {
                $w->whereNull('translated_to')
                    ->orWhere('translated_to', '!=', $readerLocale);
            });
            if (Schema::hasTable('grimba_post_translations')) {
                $q->whereNotExists(function ($sub) use ($readerLocale) {
                    $sub->selectRaw('1')
                        ->from('grimba_post_translations')
                        ->whereColumn('grimba_post_translations.post_id', 'posts.id')
                        ->where('grimba_post_translations.locale', $readerLocale)
                        ->whereNotNull('grimba_post_translations.translated_name');
                });
            }
        });

        return $query->count();
    };

    // Try cached path first; fall through to fresh compute on any
    // cache backend failure. Display surfaces never block on cache.
    try {
        $tailCount = (int) Cache::remember($cacheKey, 60, $__tailComputeFresh);
    } catch (\Throwable $e) {
        report($e);
        $tailCount = (int) $__tailComputeFresh();
    }

    if ($tailCount === 0) {
        return;
    }

    $toggleUrl = url(request()->path()) . '?lang=' . urlencode($opposite);
    // Copy is driven by the READER locale, not the framework locale —
    // app()->getLocale() returns the framework default before the
    // page renders, so __() would surface French copy on an English
    // page. Inlining both variants keeps the ribbon coherent with
    // resolveReaderLocale()'s value.
    // Display-cap: a count above 99 reads as "99+" so the ribbon
    // doesn't shout "737 articles!" at the reader. The full count
    // is still visible to scrapers via the title attribute.
    $displayCount = $tailCount > 99 ? '99+' : (string) $tailCount;
    if ($readerLocale === 'en') {
        $countLabel = $tailCount === 1
            ? $displayCount . ' article also available in French'
            : $displayCount . ' articles also available in French';
        $hintCopy = 'The language filter hides untranslated stories — click to see them.';
        $ctaCopy = 'View in French →';
    } else {
        $countLabel = $tailCount === 1
            ? $displayCount . ' article aussi disponible en anglais'
            : $displayCount . ' articles aussi disponibles en anglais';
        $hintCopy = 'Le filtre langue masque les articles non traduits — cliquez pour les voir.';
        $ctaCopy = 'Voir en anglais →';
    }
@endphp

<section class="grimba-tail-expander" data-grimba-tail-expander data-surface="{{ $surface }}" lang="{{ $readerLocale }}" aria-label="{{ $readerLocale === 'en' ? 'Also available in another language' : 'Aussi disponible dans une autre langue' }}">
    <div class="grimba-tail-expander__inner">
        <span class="grimba-tail-expander__icon" aria-hidden="true">⇄</span>
        <p class="grimba-tail-expander__copy">
            {{ $countLabel }}.
            <span class="grimba-tail-expander__hint">{{ $hintCopy }}</span>
        </p>
        <a class="grimba-tail-expander__cta" href="{{ $toggleUrl }}" hreflang="{{ $opposite }}" lang="{{ $opposite }}">
            {{ $ctaCopy }}
        </a>
    </div>
</section>

@once
    <style>
        .grimba-tail-expander {
            margin: 32px auto;
            max-width: 920px;
            padding: 0 16px;
        }
        .grimba-tail-expander__inner {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 18px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(246, 241, 232, .92), rgba(255, 255, 255, .88));
            border: 1px solid rgba(192, 57, 43, .14);
            box-shadow: 0 12px 32px rgba(26, 23, 19, .08);
            backdrop-filter: blur(8px) saturate(1.1);
            -webkit-backdrop-filter: blur(8px) saturate(1.1);
        }
        .grimba-tail-expander__icon {
            font-size: 22px;
            line-height: 1;
            color: rgba(192, 57, 43, .82);
            flex: 0 0 auto;
        }
        .grimba-tail-expander__copy {
            flex: 1 1 auto;
            margin: 0;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: var(--gn-ink, #14110d);
        }
        .grimba-tail-expander__hint {
            display: block;
            font-size: 12.5px;
            opacity: .68;
            margin-top: 2px;
        }
        .grimba-tail-expander__cta {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 16px;
            border-radius: 999px;
            background: #14110d;
            color: #fffaf1;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-weight: 800;
            font-size: 13px;
            letter-spacing: .02em;
            text-decoration: none;
            transition: transform .18s ease, filter .18s ease;
        }
        .grimba-tail-expander__cta:hover,
        .grimba-tail-expander__cta:focus-visible {
            color: #fffaf1;
            text-decoration: none;
            transform: translateY(-1px);
            filter: brightness(1.08);
        }
        @media (max-width: 600px) {
            .grimba-tail-expander__inner {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .grimba-tail-expander__cta {
                align-self: stretch;
            }
        }
        [data-bs-theme="dark"] .grimba-tail-expander__inner,
        body[data-theme="dark"] .grimba-tail-expander__inner {
            background: linear-gradient(135deg, rgba(28, 24, 17, .82), rgba(40, 35, 28, .76));
            border-color: rgba(255, 154, 138, .22);
            color: #fffaf0;
        }
        [data-bs-theme="dark"] .grimba-tail-expander__copy,
        body[data-theme="dark"] .grimba-tail-expander__copy {
            color: #fffaf0;
        }
        [data-bs-theme="dark"] .grimba-tail-expander__cta,
        body[data-theme="dark"] .grimba-tail-expander__cta {
            background: #fffaf1;
            color: #14110d;
        }
    </style>
@endonce
