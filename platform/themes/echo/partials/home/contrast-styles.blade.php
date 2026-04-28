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
            radial-gradient(circle at 18% 52%, rgba(0, 0, 0, .58), transparent 45%),
            linear-gradient(90deg, rgba(0, 0, 0, .78) 0%, rgba(0, 0, 0, .46) 42%, rgba(0, 0, 0, .18) 100%),
            linear-gradient(0deg, rgba(0, 0, 0, .72) 0%, rgba(0, 0, 0, .08) 46%);
    }

    .grimba-hero__gradient {
        z-index: 1;
        background:
            linear-gradient(180deg, rgba(0, 0, 0, .08) 0%, rgba(0, 0, 0, .82) 100%),
            linear-gradient(90deg, rgba(0, 0, 0, .78) 0%, rgba(0, 0, 0, .26) 70%);
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

    .grimba-hero__desc {
        max-width: 760px;
        margin-top: 16px;
        font-size: clamp(17px, 2vw, 22px);
        line-height: 1.45;
        font-weight: 600;
    }

    .grimba-hero__coverage .grimba-coverage__chip,
    .grimba-hero__coverage .grimba-coverage__sources {
        background: rgba(0, 0, 0, .48) !important;
        border-color: rgba(255, 255, 255, .28) !important;
        backdrop-filter: blur(8px);
    }
</style>
