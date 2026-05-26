# GrimbaNews — App Store Optimization Plan

**Status:** plan v0 (no store listing — gates on S1153 wrapper + S1160 launch)
**Owner:** Gary Vaynerchuk (CMO) chairs strategy + Olivia Davis (Marketing Strategist) on keyword research + Alex Morgan (UI/UX) on screenshots + Henry Walker on description copy
**Walks:** Mythos S1178 (App Store Optimization) deferred → partial
**Gating dependency:** Store listing exists (gates on S1153 + S1160)

## Why this exists

S1178 is the discoverability layer in App Store + Play Store search. Without ASO discipline, the app ranks for nothing and competes with established outlets (Le Monde, NYTimes, BBC).

## Today's surrogate

- **Web SEO** — `<title>`, `<meta>` tags, JSON-LD per page. Strong on terms like "GrimbaNews", "biais média France", "MG indicator", "agrégateur source diverse".
- **Hreflang wiring** — `docs/GRIMBANEWS_DE_HREFLANG_WIRING.md`, `docs/GRIMBANEWS_ES_HREFLANG_WIRING.md`, `docs/GRIMBANEWS_PT_BR_HREFLANG_WIRING.md`.

## Keyword research (Olivia Davis)

| Locale | Primary | Secondary | Long-tail |
|---|---|---|---|
| fr-FR | actualités sans biais | revue de presse, sources diverses | comparateur de média, juste milieu information |
| en-US | unbiased news | news bias rating, diverse sources | middle ground news app, news media comparison |
| en-GB | bbc alternative news | news bias score | left right centre news app |
| es | noticias sin sesgo | medios alternativos | comparador de medios |
| pt-BR | notícias sem viés | meios alternativos | mídia alternativa Brasil |

(Per-locale listings ship as part of per-locale launch ops per `GRIMBANEWS_MULTI_LANGUAGE_LAUNCH_OPS.md`.)

## Listing assets

### iOS App Store

| Field | Spec | Owner |
|---|---|---|
| App name | "GrimbaNews" (max 30 chars) | locked |
| Subtitle | "Read across the bias spectrum" (max 30 chars) | Henry Walker |
| Description | 4000 chars, first 252 above-fold | Henry Walker |
| Keywords | 100-char comma-separated | Olivia Davis |
| Promotional text | 170 chars, editable post-launch | Henry Walker |
| Screenshots | 6.7", 6.5", 5.5" (3 device sets) | Alex Morgan |
| App preview video | 15-30s, portrait | Alex Morgan + Maria Lopez |

### Google Play Console

| Field | Spec | Owner |
|---|---|---|
| App title | "GrimbaNews" (max 30 chars) | locked |
| Short description | 80 chars | Henry Walker |
| Full description | 4000 chars | Henry Walker |
| Feature graphic | 1024x500 | Alex Morgan |
| Screenshots | min 2, phone + tablet | Alex Morgan |
| Promo video | YouTube link | Alex Morgan |

## A/B test cadence

- iOS Product Page Optimization — test screenshot variant per 30-day window.
- Google Store Listing Experiments — test description / icon per 14-day window.
- Track conversion rate (impression → install) per variant.

## Localization

- All listing fields localized to FR, EN-US, EN-GB, ES, PT-BR at launch.
- DE, IT, AR, HE, JA, ZH, KO, RU, HI, SW added as per-locale launch ships (per locale catalog plans).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1178)
- Sister docs: `docs/GRIMBANEWS_MOBILE_APP_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_MULTI_LANGUAGE_LAUNCH_OPS.md`, `docs/GRIMBANEWS_MOBILE_APP_STORE_REVIEW_CHANNEL.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
