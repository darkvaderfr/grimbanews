<style>
    .grimba-ad-wrap {
        margin-block: 18px;
        color: var(--gn-muted, #746f66);
        max-width: 100%;
    }

    .grimba-ad-wrap__label {
        display: block;
        margin-bottom: 6px;
        font-family: "JetBrains Mono", ui-monospace, monospace;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .16em;
        text-align: center;
        text-transform: uppercase;
        opacity: .62;
    }

    .grimba-ad-slot {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 92px;
        padding: 10px;
        border: 1px solid rgba(26, 23, 19, .10);
        border-radius: 24px;
        background: rgba(255, 255, 255, .62);
        box-shadow: 0 18px 44px rgba(26, 23, 19, .06);
        overflow: hidden;
        width: 100%;
        /* Wave ZZZZZZZZ (R-14 — Vader 2026-05-22) — Lighthouse CLS
           defense. content-visibility:auto lets the browser skip
           layout + render for off-viewport ads until they near
           the viewport, while contain-intrinsic-size guarantees
           the placeholder size for layout reservation. Eliminates
           the "ad loads and pushes content down" CLS pattern that
           kills Lighthouse mobile scores. */
        content-visibility: auto;
        contain-intrinsic-size: auto 92px;
    }

    .grimba-ad-slot--billboard,
    .grimba-ad-slot--leaderboard {
        min-height: 112px;
        /* Wave ZZZZZZZZ — leaderboards are 728x90 + padding =
           112px; lock the intrinsic-size hint to that. */
        contain-intrinsic-size: auto 112px;
    }

    .grimba-ad-slot--native {
        min-height: 180px;
        border-style: dashed;
        contain-intrinsic-size: auto 180px;
    }

    .grimba-ad-slot--sidebar {
        /* Wave ZZZZZZZZ — bumped from 250px to 270px to account
           for the AdSense 300x250 medium-rectangle + container
           padding. 250px was clipping the 1px-2px below the ad
           on Safari. */
        min-height: 270px;
        align-items: flex-start;
        contain-intrinsic-size: auto 270px;
    }

    .grimba-ad-slot ins.adsbygoogle {
        display: block;
        width: 100%;
        min-height: inherit;
    }

    .grimba-direct-ad {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 14px;
        align-items: center;
        width: 100%;
        min-height: inherit;
        color: #1b1916;
        text-decoration: none;
    }

    .grimba-direct-ad:hover,
    .grimba-direct-ad:focus-visible {
        color: #11100e;
        text-decoration: none;
    }

    .grimba-direct-ad__signal {
        width: 42px;
        height: 42px;
        border-radius: 16px;
        background:
            radial-gradient(circle at 32% 28%, rgba(255, 255, 255, .92), transparent 32%),
            linear-gradient(135deg, rgba(69, 132, 255, .94), rgba(199, 57, 47, .86));
        box-shadow: 0 14px 32px rgba(69, 132, 255, .22);
    }

    .grimba-direct-ad__copy {
        display: grid;
        gap: 2px;
        min-width: 0;
        text-align: left;
    }

    .grimba-direct-ad__copy strong {
        font-size: 15px;
        line-height: 1.2;
        letter-spacing: 0;
    }

    .grimba-direct-ad__copy span {
        color: var(--gn-muted, #746f66);
        font-size: 13px;
        line-height: 1.35;
    }

    .grimba-direct-ad__cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 0 14px;
        border-radius: 999px;
        background: #14110d;
        color: #fffaf1;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    [data-bs-theme="dark"] .grimba-ad-slot {
        border-color: rgba(255, 255, 255, .10);
        background: rgba(255, 255, 255, .06);
        box-shadow: 0 20px 54px rgba(0, 0, 0, .24);
    }

    [data-bs-theme="dark"] .grimba-direct-ad {
        color: #fff7e8;
    }

    [data-bs-theme="dark"] .grimba-direct-ad__copy span {
        color: rgba(255, 247, 232, .72);
    }

    [data-bs-theme="dark"] .grimba-direct-ad__cta {
        background: #fff7e8;
        color: #16120e;
    }

    @media (max-width: 575.98px) {
        .grimba-ad-slot {
            border-radius: 20px;
            padding: 12px;
        }

        .grimba-direct-ad {
            grid-template-columns: auto minmax(0, 1fr);
        }

        .grimba-direct-ad__cta {
            grid-column: 1 / -1;
            width: 100%;
        }

        /* Mobile feeds skip the in-feed native slot — phones already see
           home_top + home_mid + home_native around the feed. Keeps the
           editorial list breathing without losing inventory. */
        .grimba-latest__ad {
            display: none;
        }
    }

    /* Direct-card hover polish so the house fallback doesn't sit static
       beside the editorial cards. */
    .grimba-direct-ad {
        transition: transform .2s ease, filter .2s ease;
    }

    .grimba-direct-ad:hover,
    .grimba-direct-ad:focus-visible {
        transform: translateY(-1px);
        filter: brightness(1.02);
    }

    .grimba-direct-ad__signal {
        transition: transform .4s ease;
    }

    .grimba-direct-ad:hover .grimba-direct-ad__signal,
    .grimba-direct-ad:focus-visible .grimba-direct-ad__signal {
        transform: rotate(-3deg) scale(1.06);
    }

    /* In-feed slot — slightly tighter than free-standing natives so it
       slots into the latest list rhythm without breaking flow. */
    .grimba-ad-slot--in-feed {
        min-height: 132px;
        border-style: solid;
    }

    /* Below-fold lazy hint: AdSense's own viewability heuristics still
       drive ad loading, but `content-visibility: auto` lets the browser
       skip rendering work for off-screen slots. */
    [data-grimba-ad-lazy="lazy"] {
        content-visibility: auto;
        contain-intrinsic-size: 0 132px;
    }
</style>
