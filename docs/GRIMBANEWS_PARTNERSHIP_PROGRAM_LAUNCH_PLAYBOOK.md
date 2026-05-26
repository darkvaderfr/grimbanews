# GrimbaNews — Partnership Program Launch Playbook

**Status:** playbook v0 (program not launched; gates on S1321-S1329)
**Owner:** Lucy Leai (Strategy) + Steve Jobs (CPO) on UX + Ray Dalio (CFO) on terms + Liam Smith (PM) on rollout
**Walks:** Mythos S1330 (partnership program launch) deferred → partial
**Gating dependency:** First signed partner per `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md` + `docs/GRIMBANEWS_SYNDICATION_AGREEMENT_TEMPLATE.md`. Playbook itself is operator-side.

## Why this exists

S1330 was honest-deferred as gating on S1321-S1329. The launch playbook itself — what we publish, in what order, with what success criteria — is operator-side scope and **doesn't depend on signed partners**. This document sequences the launch so when the first partner signs the announcement / page / metrics dashboard are ready, not invented under deadline pressure.

## Launch phases

### Phase 0 — Prep (operator, no partner signed yet)

1. **Templates ready** — partnership template + syndication agreement template + DPA-data-clause boilerplate. (Today: templates exist per `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md` + `docs/GRIMBANEWS_SYNDICATION_AGREEMENT_TEMPLATE.md`.)
2. **BD outreach list** — Lucy's target newsrooms (DOM-TOM, Africa francophone, Africa anglophone, Caribbean, diaspora). 10-15 named targets.
3. **Per-partner stream filter** (S1323) shipped behind feature flag.
4. **Partner-badge UI** (S1324) shipped behind feature flag.
5. **Per-partner onboarding doc generator** (S1328 work) — script in `app/Console/Commands/GrimbaPartnerOnboard.php` (new — gates on first partner).

### Phase 1 — First partner signed

1. **Internal launch announcement** — Iboga ops channel + Vader review.
2. **Onboarding** — run per-partner onboarding checklist per `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md` "Onboarding checklist" section.
3. **30-day soft-launch** — partner content flowing through with close monitoring; no external announcement.
4. **Lucy + lead editor weekly review** — cluster placements, bias-classifier flags, reader feedback.
5. **First-partner case study draft** (S1329 deferred → starts here).

### Phase 2 — Public announcement (single partner)

1. **`/partners` landing page** ships — gates on Steve Jobs design review. Single partner card.
2. **Press release** — Lucy + Vader review.
3. **Partner cross-promo** — partner announces on their property simultaneously.
4. **Newsletter announcement** — vault-digest mail (week of launch).
5. **Per-partner badge** enabled on partner-tagged content.

### Phase 3 — Expand to 3-5 partners

1. **Repeat Phase 1 + Phase 2** per new partner.
2. **`/partners` landing page** grows to 3-5 cards.
3. **Per-partner analytics** (S1325) shipped — operator-side dashboard.
4. **Per-partner SLA tracking** (S1327) — operator-side.

### Phase 4 — Open intake

1. **`/partners/apply` form** opens — self-serve partner application.
2. **Lucy reviews queue** weekly.
3. **Standardized partnership terms** offered; bespoke terms still negotiable.
4. **Quarterly partnership review** — Lucy + Ray + lead editor.

## Success criteria

| Phase | Metric | Target |
|---|---|---|
| Phase 1 | Partner content live without ingest errors | 100% (zero ingest failures over 30d) |
| Phase 1 | Reader feedback negative-to-positive ratio | < 1:5 |
| Phase 2 | `/partners` page traffic | > 500 visits in launch week |
| Phase 3 | Partner count | 3-5 |
| Phase 3 | Partner-tagged content as % of total ingest | 5-15% |
| Phase 4 | Application-to-signed conversion | > 25% |
| Phase 4 | Per-partner monthly active retention | > 90% |

## Launch surfaces

### `/partners` landing page

- **Hero:** "Newsrooms we work with."
- **Per-partner card:** logo, name, country, tier badge, "Read [Partner] coverage" link.
- **Methodology cross-link** to `/methodologie` page.
- **CTA at bottom:** "Want to syndicate?" → `/partners/apply` (Phase 4).

### Per-partner content filter

- URL pattern: `/partenaire/{partner-slug}` (FR primary) + `/partner/{partner-slug}` (EN).
- Filters `posts` where `news_sources.partner_slug = {slug}` (new column, gates on S1323).
- Same chrome as `/categorie/{cat}` listing.

### Per-partner badge

- Card variant added to `partials/post-card.blade.php`.
- Renders partner logo + "Via [Partner Name]" tag below source-logo.
- Gates on `news_sources.partner_slug IS NOT NULL`.

## Communications cadence

- **Pre-launch** — silent during Phase 0 + Phase 1 soft-launch.
- **Phase 2 public** — coordinated announcement; partner-vetted copy.
- **Ongoing** — quarterly "partners spotlight" in vault-digest mail.

## Risk register

| Risk | Mitigation |
|---|---|
| Partner content feed breaks → reader sees error | `App\Console\Commands\GrimbaFetchRssFeeds` already retries; per-source error budget on `news_sources.last_error` (existing). |
| Partner content fails brand-safety | `App\Support\GrimbaIngestGuardrails` keyword filter; flag for editorial review. |
| Partner objects to bias-classifier rating | Lucy / ombudsman review; rating can be adjusted with documented rationale. |
| Partner objects to cluster placement | Ombudsman per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`. |
| Partner content over-dominates feed | Per-source daily cap; surface in cockpit board. |
| Partner depublishes content (e.g. court order) | 24h takedown SLA per syndication agreement; soft-delete with operator-recorded rationale. |

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1330; gates on S1321-S1329)
- Sister docs: `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`, `docs/GRIMBANEWS_SYNDICATION_AGREEMENT_TEMPLATE.md`, `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`, `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md`
- Source seeder + ingest: `database/seeders/RssFeedsSeeder.php`, `app/Console/Commands/GrimbaFetchRssFeeds.php`
- Brand-safety filter: `app/Support/GrimbaIngestGuardrails.php`
- Cockpit board: `resources/views/grimba-admin/cockpit.blade.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
