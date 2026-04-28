<style>
    .grimba-ad-wrap {
        margin-block: 18px;
        color: var(--gn-muted, #746f66);
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
    }

    .grimba-ad-slot--billboard,
    .grimba-ad-slot--leaderboard {
        min-height: 112px;
    }

    .grimba-ad-slot--native {
        min-height: 180px;
        border-style: dashed;
    }

    .grimba-ad-slot ins.adsbygoogle {
        display: block;
        width: 100%;
        min-height: inherit;
    }

    [data-bs-theme="dark"] .grimba-ad-slot {
        border-color: rgba(255, 255, 255, .10);
        background: rgba(255, 255, 255, .06);
        box-shadow: 0 20px 54px rgba(0, 0, 0, .24);
    }
</style>
