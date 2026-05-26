# GrimbaNews — Spanish (ES) Launch Readiness Checklist

**Status:** checklist v0 (per-locale ops gate; gates on S1101-S1109 catalog + landing + editorial + hreflang shipping)
**Owner:** Lucy Leai (CEO) on go/no-go + Liam Smith (PM) on coordination + Sara Kim (QA) on smoke sign-off
**Walks:** Mythos S1110 (ES launch readiness) deferred → partial
**Gating dependency:** All S1101-S1109 rows shipped first. Operator + ES-native reviewer + Comms approval required.

## Why this exists

S1110 honest-deferred on "gates on S1101-S1109 + per-locale ops." Those are real dependencies. The **checklist itself** is operator-side and can be authored now so when catalog + landing + editorial + hreflang ship, the launch sequence is mechanical, not improvised.

## T-14 checklist

- [ ] ES catalog (`lang/es.json`) merged, reviewed by ES-native reviewer
- [ ] ES landing (`/es`) live on staging
- [ ] ES editorial category pages (`/es/categoria/{slug}`) live on staging
- [ ] hreflang ES tags emitting on staging
- [ ] Sitemap regenerated with ES URLs
- [ ] Robots.txt allows /es/* crawl
- [ ] Internal comms drafted (Henry Walker — content + outreach)
- [ ] LinkedIn / X / Mastodon ES launch posts drafted (Maria Lopez)
- [ ] Press list updated with ES-language outlets

## T-7 checklist

- [ ] Per-route Playwright smoke (Sara Kim): `/es`, `/es/categoria/politica`, `/es/dossier/{id}`, `/es/blog/{slug}`
- [ ] Lighthouse SEO + Performance + Accessibility on `/es` >= 90 each
- [ ] axe-core a11y scan on `/es` returns zero violations
- [ ] Email digest renders correctly for `locale=es` members (preview render in inbox)
- [ ] Google Search Console verified for `grimbanews.com/es/*` paths

## T-1 checklist

- [ ] Submit ES sitemap to Google Search Console
- [ ] Submit ES sitemap to Bing Webmaster Tools
- [ ] Confirm Plausible / GA shows `/es` paths in per-page reports
- [ ] CDN cache pre-warmed for `/es` paths
- [ ] On-call (Jacob Lee + Hannah Kim) briefed on potential locale-routing issues

## T-0 launch day

- [ ] Flip ES from staging to prod
- [ ] Smoke `/es` from clean browser (no cookies)
- [ ] Smoke language switcher FR → EN → ES on production
- [ ] Smoke `/account` preference center honors ES locale
- [ ] Publish launch announcement
- [ ] Monitor `grimba_automation_runs` for new error patterns (ES-tagged)

## T+7 retro

- [ ] Compare ES-locale conversion vs FR/EN baseline
- [ ] Review search-query log for ES queries on canonical URLs (cross-locale fall-throughs)
- [ ] Editorial reviewer feedback log
- [ ] Capture lessons for next-locale launch (PT-BR per S1120)

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1110 row)
- Sister docs: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_ES_LANDING_PAGE_SCOPE.md`, `docs/GRIMBANEWS_ES_EDITORIAL_PAGES_SCOPE.md`, `docs/GRIMBANEWS_ES_HREFLANG_WIRING.md`, `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md`
- Existing infrastructure: same as sister docs
