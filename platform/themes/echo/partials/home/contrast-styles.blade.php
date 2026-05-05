<style>
    .grimba-hero__media {
        isolation: isolate;
        background: #111;
    }

    /* S349 — softer hero overlay. Was sized for vivid news photos
       with crops at the bottom, but crushed clear / well-lit hero
       images. Pulled overlay opacities back ~30% on average — text-
       shadow on titles still keeps headlines readable. */
    .grimba-hero__media::after,
    .grimba-section__hero::after,
    .grimba-blind-card::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
        background:
            radial-gradient(circle at 20% 56%, rgba(0, 0, 0, .54), transparent 52%),
            linear-gradient(90deg, rgba(0, 0, 0, .60) 0%, rgba(0, 0, 0, .38) 52%, rgba(0, 0, 0, .14) 100%),
            linear-gradient(0deg, rgba(0, 0, 0, .58) 0%, rgba(0, 0, 0, .10) 56%);
    }

    .grimba-hero__gradient {
        z-index: 1;
        background:
            linear-gradient(180deg, rgba(0, 0, 0, .06) 0%, rgba(0, 0, 0, .64) 100%),
            linear-gradient(90deg, rgba(0, 0, 0, .60) 0%, rgba(0, 0, 0, .20) 76%);
    }

    .grimba-hero__text,
    .grimba-hero__coverage,
    .grimba-section__hero-body,
    .grimba-blind-card__body {
        z-index: 2;
    }

    .grimba-hero__text,
    .grimba-section__hero-body {
        background:
            linear-gradient(135deg, rgba(11, 10, 8, .93), rgba(11, 10, 8, .78)),
            radial-gradient(circle at 0% 0%, rgba(255, 255, 255, .16), transparent 36%) !important;
        border: 1px solid rgba(255, 255, 255, .22);
        box-shadow: 0 24px 60px rgba(0, 0, 0, .40);
        color: #fff !important;
    }

    .grimba-hero__title,
    .grimba-section__hero-title,
    .grimba-blind-card__title {
        color: #fff !important;
        text-shadow: 0 3px 18px rgba(0, 0, 0, .72), 0 1px 2px rgba(0, 0, 0, .92);
    }

    .grimba-hero__desc,
    .grimba-section__kicker,
    .grimba-hero__coverage,
    .grimba-hero__coverage .grimba-coverage,
    .grimba-hero__coverage .grimba-coverage__sources,
    .grimba-hero__coverage .grimba-coverage__chip,
    .grimba-hero__coverage .grimba-coverage__label,
    .grimba-blind-card__body,
    .grimba-blind-card__body .grimba-coverage,
    .grimba-blind-card__body .grimba-coverage__sources {
        color: rgba(255, 255, 255, .92) !important;
        text-shadow: 0 2px 12px rgba(0, 0, 0, .78), 0 1px 2px rgba(0, 0, 0, .9);
    }

    .grimba-hero .grimba-hero__desc {
        display: block;
        max-width: min(760px, 100%);
        margin-top: 12px;
        padding: 0;
        font-size: clamp(16px, 1.7vw, 20px);
        line-height: 1.45;
        font-weight: 700;
        color: #fff !important;
        background: transparent;
        border: 0;
        border-radius: 0;
        box-shadow: none;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    .grimba-hero__coverage .grimba-coverage__chip,
    .grimba-hero__coverage .grimba-coverage__sources {
        background: rgba(0, 0, 0, .48) !important;
        border-color: rgba(255, 255, 255, .28) !important;
        backdrop-filter: blur(8px);
    }

    /* Fallback cards (img.gn-placeholder swapped in by front-body-hooks
       when an external publisher image fails to load). The cream/serif
       editorial placeholder needs a dramatically lighter gradient so
       the headline + source label remain visible. */
    .grimba-hero__media.gn-fallback-card::after,
    .grimba-section__hero.gn-fallback-card::after,
    .grimba-blind-card.gn-fallback-card::after {
        background: linear-gradient(180deg, transparent 50%, rgba(11, 10, 8, .22) 100%);
    }
    .grimba-hero__media.gn-fallback-card .grimba-hero__gradient {
        background: linear-gradient(180deg, transparent 55%, rgba(11, 10, 8, .26) 100%);
    }
    .grimba-hero__media.gn-fallback-card .grimba-hero__text,
    .grimba-section__hero.gn-fallback-card .grimba-section__hero-body {
        background: linear-gradient(135deg, rgba(11, 10, 8, .82), rgba(11, 10, 8, .68)) !important;
    }
</style>
