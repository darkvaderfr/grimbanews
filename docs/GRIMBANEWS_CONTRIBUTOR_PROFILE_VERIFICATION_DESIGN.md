# GrimbaNews — Contributor Profile + Verification Design

**Sprint ID:** S1452
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor profile + verification`
**Walk wave:** BBBB

## Gating dependency

A contributor profile + verification flow needs:

- Author system live (S1411, deferred)
- A `contributor_profiles` table extending `users` (bio, social-handles, verification-state, KYC-state where paid)
- Identity verification (Persona / Veriff / Stripe Identity)
- Profile-photo upload (privacy-safe, no biometric capture)
- Per-contributor public page `/contributeurs/{slug}` (FR) / `/contributors/{slug}` (EN)
- Per-locale catalog entries

## Surrogate-now infra

- **Botble `users` table** — admin-only authors today; contributor extension is additive
- **`docs/GRIMBANEWS_CONTRIBUTOR_SUBMISSION_PORTAL_DESIGN.md`** — existing partial sibling
- **Per-author byline shipped (Wave DDDD)** — `<x-grimba-byline>` already renders author attribution

## Honest framing

Profile + verification gates on author system (S1411). KYC adds operator + compliance cost; non-KYC profiles ship in 1 week once author system lands.

## Owners

- **Backend:** Rajesh Kumar — `contributor_profiles` schema
- **Frontend:** Nina Patel — profile page + upload flow
- **Compliance:** Maya Patel — KYC + photo-storage policy
- **CISO:** Sara Chen — identity-provider integration
- **Product:** Liam Smith — verification levels (email-only / KYC)
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1452 row)
- Existing submission portal: `docs/GRIMBANEWS_CONTRIBUTOR_SUBMISSION_PORTAL_DESIGN.md`
- Rate card: `docs/GRIMBANEWS_CONTRIBUTOR_RATE_CARD_DESIGN.md`
- 1099 / tax reporting: `docs/GRIMBANEWS_CONTRIBUTOR_1099_TAX_REPORTING_DESIGN.md`
- Analytics: `docs/GRIMBANEWS_CONTRIBUTOR_ANALYTICS_DASHBOARD_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
