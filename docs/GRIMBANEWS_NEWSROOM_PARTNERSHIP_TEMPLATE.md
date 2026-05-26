# GrimbaNews — Newsroom Partnership Template

**Status:** template v0 (no partnerships signed; operator-side BD pickup)
**Owner:** Lucy Leai (Strategy) + Ray Dalio (CFO) on revenue-share + retained counsel for contract
**Walks:** Mythos S1321 (newsroom partnership doc), S1328 (per-partner onboarding) deferred → partial
**Gating dependency:** First newsroom partner conversation + counsel review of partnership terms. Template itself is operator-side.

## Why this exists

S1321 + S1328 share a root: GrimbaNews has the **content-aggregation pipeline** today (RSS / news API ingest + cluster engine) but no formalized newsroom-partnership program. There is no template to send a prospective partner; no onboarding checklist; no per-partner stream tag. The deferral note flagged "operator-side BD pickup" — this doc provides the template and onboarding checklist so BD has a starting position, not a blank page.

## Today's relevant infrastructure

- `news_sources` table holds source metadata. Operator-added via `/admin/grimba/news-sources` + seeded via `RssFeedsSeeder.php`.
- `news_sources.license_notes` slot exists per `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md` — used to record per-source license terms.
- Per-source content stream is **partial today** (S1323): `RssFeedsSeeder` per-source pattern is the data hook; partner-tagged stream filtering deferred.

## Partnership tiers

| Tier | What partner provides | What GrimbaNews provides | Cost flow |
|---|---|---|---|
| Syndication | Full-text feed via RSS / API | Credited republication + canonical link back + per-partner badge (S1324 dep) | Free / flat fee TBD |
| Co-publishing | Mutual editorial agreement + shared coverage plan | Co-byline; appears on both sites simultaneously | No money / cross-promo |
| Editorial fellowship | Hosts a GrimbaNews editor for N weeks | Reciprocal editorial cross-pollination | Operator-side budget |
| White-label digest | Curated daily digest under partner brand | Per-edition email send via LeafRelay | Per-seat fee TBD |
| Embed widget license | (gates on S1651) | Branded embed widget for partner site | Per-domain fee TBD |

## Partnership doc — outline (template)

A signed partnership doc carries 9 sections:

### 1. Parties
- GrimbaNews (Iboga Ventures — operator) + Partner Newsroom (legal name, jurisdiction).

### 2. Scope of partnership
- Tier selected (one of the 5 above).
- Effective dates.
- Geographic / language scope.

### 3. Content rights
- Partner grants: republication rights + image rights (specify).
- GrimbaNews grants: attribution + canonical link back + brand placement.
- Neither party gains: copyright transfer.

### 4. Attribution + branding
- "Per-partner badge" placement (gates on S1324 partner-attribution UI work).
- Per-source logo via `news_sources.logo_url` (existing field via image-proxy).
- Tagline format: "Originally published in [Partner Name] — [Date]".

### 5. Frequency + volume
- Expected daily / weekly volume.
- Cap (per-day, per-week) to avoid feed-domination.
- Per-source rate limit honored at ingest layer.

### 6. Quality + style adherence
- Partner-content runs through `App\Services\GrimbaBiasClassifier` (informational, not blocking).
- Partner pieces flagged in `news_sources.editorial_category` and `news_sources.credibility_score`.
- Editorial-style-guide adherence requested but not enforced on partner content.

### 7. Revenue share (if applicable)
- For paid newsletter tier (per `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`): split-formula TBD per S1326 dependency.
- For ads on partner-tagged pages: split per S1326.
- Stripe Connect routing (gates on Stripe onboarding per `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md`).

### 8. Termination
- Either party may terminate with 30 days notice.
- On termination: partner content unpublished from GrimbaNews within 7 days; per-partner badge removed.
- Surviving clauses: attribution for already-published archives, mutual non-disparagement.

### 9. Governance + dispute
- Ombudsman per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` handles editorial disputes.
- Commercial disputes → counsel.

## Onboarding checklist (S1328)

Per-partner onboarding — operator runs this once per signed partnership:

1. **Counsel review** of executed partnership doc → archive to `storage/app/partnerships/{partner-slug}/contract.pdf`.
2. **DPA / data clause review** if partner shares any reader data (typically no — only content flows).
3. **Vendor register update** — add partner row to `docs/GRIMBANEWS_VENDOR_REGISTER.md` if data flow is material.
4. **Source seeding** — append partner's RSS / API endpoint to `RssFeedsSeeder.php` with `editorial_category`, `country`, `credibility_score`, `factuality_score`, `bias_rating`, `license_notes` populated.
5. **Per-source classification check** — run `php artisan grimba:classify-sources --source-id={id}` (per `app/Console/Commands/GrimbaClassifySources.php`).
6. **Partner badge config** (gates on S1324 — placeholder for now).
7. **Per-partner content stream URL** (gates on S1323 partner-tagged-stream filter — placeholder).
8. **Revenue-share Stripe Connect setup** (if S1326 ships).
9. **Editorial brief shared** with partner — per `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md` orientation.
10. **Soft-launch monitoring** — 30 days of close observation: cluster placements, bias-classifier flags, reader feedback via `/contact`.
11. **Quarterly review** schedule entered into editorial calendar (S1316).

## Sample partner targets (Lucy's BD list, illustrative)

- **DOM-TOM newsrooms** — France-Antilles, RCI Guadeloupe, Tahiti-Infos. Tier: Syndication. Cost: free in exchange for distribution.
- **Africa francophone** — Le Pays (Burkina), Jeune Afrique (already partial ingestion). Tier: Syndication.
- **Africa anglophone** — Daily Maverick (SA), Premium Times (NG), The Continent. Tier: Syndication or Co-publishing.
- **Caribbean** — Stabroek News (Guyana). Tier: Syndication.
- **Diaspora media** — relevant North American francophone press. Tier: White-label digest or Embed.

## Engineering effort to ship full partnership program

- Per-partner stream tag (S1323 ship): 2 sprints.
- Partner-badge UI (S1324 ship): 1 sprint.
- Per-partner analytics (S1325 ship): 3 sprints.
- Revenue-share Stripe Connect (S1326 ship): gates on Stripe scope.
- Per-partner onboarding doc generator: 1 sprint.
- **Full ship: ~10-12 sprints once first partner signed.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1321, S1328; gates for S1322-S1330)
- Sister docs: `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md` (per-source intake), `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md` (revenue split context), `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` (dispute handling), `docs/GRIMBANEWS_VENDOR_REGISTER.md` (subprocessor update)
- Source seeder: `database/seeders/RssFeedsSeeder.php`
- Classifier: `app/Services/GrimbaCategoryClassifier.php`, `app/Console/Commands/GrimbaClassifySources.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
