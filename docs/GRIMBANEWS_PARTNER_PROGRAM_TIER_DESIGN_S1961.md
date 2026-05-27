# GrimbaNews — Partner Program Tier Design (Free RSS / Paid API / Co-Brand)

**Sprint ID:** S1961
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner program tier design (free RSS / paid API / co-brand)`
**Walk wave:** BBBB

## Gating dependency

A 3-tier partner program (free / paid / co-brand) needs:

- Tier definitions documented + priced (Lucy + Ray decision)
- OAuth client provisioning (S1182, deferred)
- API analytics layer (S1188, deferred)
- Partner portal (S1963, deferred)
- Per-partner contract template (S1964, deferred — counsel-side)
- A `partners` table with `tier`, `status`, `signed_at`, `apikey_id`

The free RSS tier is already implicitly shipped via `/feed.xml` family. The paid + co-brand tiers gate on monetization.

## Surrogate-now infra

- **`/feed.xml` family** — `/feed.xml`, `/feed.breaking.xml`, `/feed.latest.xml`, `/feed/{categorie}` — these are the **live free-RSS partner tier**, just without per-partner attribution
- **`docs/GRIMBANEWS_PARTNER_CONTENT_SHARE_API_DESIGN.md`** — already exists; documents the paid-API tier scope
- **Per-partner case studies plan** — already exists at `docs/GRIMBANEWS_PER_PARTNER_CASE_STUDY_SCOPE.md`

## Honest framing

Free RSS is shipped — it just lacks an "official partner" badge. Paid API gates on S1182 OAuth + S1188 analytics. Co-brand gates on counsel-defined contract templates.

## Owners

- **CEO:** Lucy Leai — tier scope + brand fit
- **CFO:** Ray Dalio + Warren Buffett — pricing
- **Product:** Liam Smith — tier feature matrix
- **Backend:** Rajesh Kumar — `partners` schema
- **Biz Dev:** Victor Garcia + Chris Johnson — partner pipeline
- **Counsel (operator-side):** contract templates
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1961 row)
- Onboarding flow: `docs/GRIMBANEWS_PARTNER_ONBOARDING_FLOW_DESIGN_S1962.md`
- Portal: `docs/GRIMBANEWS_PARTNER_PORTAL_DESIGN_S1963.md`
- Analytics: `docs/GRIMBANEWS_PARTNER_ANALYTICS_DASHBOARD_DESIGN_S1967.md`
- Case studies: `docs/GRIMBANEWS_PARTNER_CASE_STUDIES_SCOPE_S1968.md`
- Content-share existing: `docs/GRIMBANEWS_PARTNER_CONTENT_SHARE_API_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
