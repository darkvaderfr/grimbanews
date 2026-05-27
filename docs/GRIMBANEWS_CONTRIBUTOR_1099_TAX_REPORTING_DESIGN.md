# GrimbaNews — Contributor 1099 / Tax Reporting Design (Per-Author Correction Tracking Sibling)

**Sprint ID:** S1457
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor 1099 / tax reporting`
**Walk wave:** BBBB

## Gating dependency

Contributor tax reporting needs:

- Billing infrastructure (S1211, deferred)
- Per-contributor tax-form intake (US W-9 / W-8BEN, EU equivalent)
- Per-contributor payment ledger
- 1099-NEC / 1042-S generation (US, ≥$600 / year threshold)
- Per-jurisdiction tax-treaty handling
- Annual reporting cadence (Jan 31 1099 deadline for US-payee, prior year)

## Surrogate-now infra

- **No payments today** — no payee = no tax reporting
- **Per-author byline (Wave DDDD shipped)** — by-line attribution is the upstream signal that a tax-reporting flow would integrate with
- **Per-author correction tracking** — sister surrogate; corrections per author are an editorial-quality signal that could feed into the contract-renewal decision

## Honest framing

Tax reporting requires real payments. Cannot ship before billing infra. The accounting-side complexity is significant; budget 3 weeks for the first 1099 season.

## Owners

- **CFO:** Ray Dalio + Warren Buffett — tax-policy ownership
- **Backend:** Rajesh Kumar — tax-form schema + ledger
- **Compliance:** Maya Patel — jurisdictional reporting
- **Counsel (operator-side):** tax treaty review
- **DBA:** Larry Ellison — payment ledger schema
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1457 row)
- Profile + verification: `docs/GRIMBANEWS_CONTRIBUTOR_PROFILE_VERIFICATION_DESIGN.md`
- Rate card: `docs/GRIMBANEWS_CONTRIBUTOR_RATE_CARD_DESIGN.md`
- Analytics: `docs/GRIMBANEWS_CONTRIBUTOR_ANALYTICS_DASHBOARD_DESIGN.md`
- Payout integration (S1456 deferred): `docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
