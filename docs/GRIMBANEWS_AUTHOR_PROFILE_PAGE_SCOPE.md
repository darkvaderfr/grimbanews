# GrimbaNews — Author Profile Page Scope

**Status:** plan v0 (no /journaliste/{slug} or /author/{slug} route)
**Owner:** Steve Jobs (CPO) signs design + Alex Morgan (UI/UX) on layout + Nina Patel (Lead FE) on Blade view + Liam Smith (PM) on field selection
**Walks:** Mythos S1412 (Author profile page) deferred → partial
**Gating dependency:** Author table (S1411) + at least one verified journalist + locale-aware byline copy

## Why this exists

S1412 is the reader-facing surface that builds journalist trust. Anonymous bylines harm credibility — readers expect "who is this person, what do they cover, can I follow them".

## Today's surrogate

- **Bylines** show admin display name (Botble user) — but no profile page to click into.
- **`/methodology`** carries editorial-policy explanation (broader trust signal).

## Routes

- FR: `/journaliste/{slug}`
- EN: `/author/{slug}`
- Hreflang annotations link both.

## Page sections (Steve Jobs design intent)

### Hero

- Photo (round mask, 160px).
- Display name + verified badge.
- Specialty chips ("Climate", "Politics").
- Locale flags.
- Social links (Twitter / Bluesky / Mastodon / LinkedIn / personal site).

### Bio block

- `bio_short` as lede paragraph.
- `bio_long` expandable.

### Recent work

- 12 most recent posts (paginated to all).
- Per-post: title, date, cluster context (if part of MG cluster), bias chip of source.

### Contribution stats (operator-cached weekly)

- "X stories published"
- "Y dossiers covered"
- "First published [date]"
- "Specialty depth: top 3 topics" — sparkline chart

### Follow CTA (gates on S1416 follow author primitive)

- "Follow {Name}" button — saves to reader account.
- Off if reader not logged in: "Sign in to follow".

### Corrections + accountability

- If `corrections_count > 0`: link to "Corrections issued on this byline (X)".
- Reader-side transparency aligns with `GRIMBANEWS_CORRECTION_POLICY_PUBLIC_PAGE_SCOPE.md`.

## SEO

- `<title>` = "{Name} — {Specialty 1}, {Specialty 2} journalist | GrimbaNews".
- JSON-LD Schema.org Person + sameAs links to social.
- Sitemap entry per active journalist.
- `og:image` = journalist photo.

## Empty / unverified states

- `verified = false`: no public profile page — 404 returned.
- `active = false`: profile preserved with "no longer contributing" notice.
- Posts authored remain attributed.

## A11y

- Photo `alt="Photo of {Name}"`.
- Verified badge has `aria-label="Verified journalist"`.
- Specialty chips are `<a>` to specialty filter pages.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1412)
- Sister docs: `docs/GRIMBANEWS_AUTHOR_TABLE_SCHEMA.md`, `docs/GRIMBANEWS_AUTHOR_FOLLOW_DESIGN.md`, `docs/GRIMBANEWS_AUTHOR_RSS_FEED_DESIGN.md`, `docs/GRIMBANEWS_AUTHOR_ANALYTICS_DASHBOARD_SCOPE.md`, `docs/GRIMBANEWS_CORRECTION_POLICY_PUBLIC_PAGE_SCOPE.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
