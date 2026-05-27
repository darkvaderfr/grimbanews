# GrimbaNews — Embed Widget Launch Playbook

**Status:** plan v0
**Owner:** Liam Smith (PM) + Nina Patel (Lead FE) + Maria Lopez (Community)
**Walks:** Mythos S1660 (embed widget — embed launch playbook) deferred → partial
**Gating dependency:** S1651-S1659 (embed widget set) all shipped + per-embed analytics + partner integration docs.

## Why this exists

The embed widget (per `GRIMBANEWS_EMBED_WIDGET_SPEC.md`) is the surface third parties use to display Grimba clusters / charts inside their own pages. Without a coordinated launch, partners try, hit edge cases, and bail.

## T-minus checklist

| Phase | Step | Owner |
|---|---|---|
| T-21d | Embed widget renders cleanly in top 5 CMSs (WordPress, Ghost, Substack, Squarespace, Webflow) | Nina Patel |
| T-14d | Docs page `/dev/embed` published with copy-paste snippets | Michael O'Connor |
| T-10d | Partner-facing analytics endpoint for embeds | David Chen |
| T-7d | Beta cohort (5 partners) | Victor Garcia |
| T-3d | CSP-friendly mode (postMessage handshake) validated | Sara Chen |
| T-0 | Public release | Liam |
| T+7 | Day-7 review | Liam / David Chen |
| T+30 | Retrospective | Liam |

## Success metrics

- ≥ 25 embeds in the wild within 90 days.
- < 1% iframe-render errors.
- Reader CTR from embed to grimbanews.com ≥ 8%.

## Cross-references

Master plan: S1660. Sister: S1651-S1659 (embed set), S1664 (SVG export), S1665 (PNG export), `GRIMBANEWS_EMBED_WIDGET_SPEC.md`.
