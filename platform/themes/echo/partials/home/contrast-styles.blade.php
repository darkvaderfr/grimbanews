<style>
    .grimba-hero__media {
        isolation: isolate;
        background: #111;
    }

    .grimba-hero__media::after,
    .grimba-section__hero::after,
    .grimba-blind-card::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
        background:
            radial-gradient(circle at 20% 54%, rgba(0, 0, 0, .78), transparent 48%),
            linear-gradient(90deg, rgba(0, 0, 0, .86) 0%, rgba(0, 0, 0, .58) 48%, rgba(0, 0, 0, .24) 100%),
            linear-gradient(0deg, rgba(0, 0, 0, .82) 0%, rgba(0, 0, 0, .18) 52%);
    }

    .grimba-hero__gradient {
        z-index: 1;
        background:
            linear-gradient(180deg, rgba(0, 0, 0, .12) 0%, rgba(0, 0, 0, .88) 100%),
            linear-gradient(90deg, rgba(0, 0, 0, .86) 0%, rgba(0, 0, 0, .34) 72%);
    }

    .grimba-hero__text,
    .grimba-hero__coverage,
    .grimba-section__hero-body,
    .grimba-blind-card__body {
        z-index: 2;
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
        width: fit-content;
        max-width: min(760px, 100%);
        margin-top: 16px;
        padding: 10px 14px;
        font-size: clamp(17px, 2vw, 22px);
        line-height: 1.45;
        font-weight: 700;
        color: #fff !important;
        background: rgba(0, 0, 0, .66);
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 18px;
        box-shadow: 0 18px 38px rgba(0, 0, 0, .34);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .grimba-hero__coverage .grimba-coverage__chip,
    .grimba-hero__coverage .grimba-coverage__sources {
        background: rgba(0, 0, 0, .48) !important;
        border-color: rgba(255, 255, 255, .28) !important;
        backdrop-filter: blur(8px);
    }
</style>
