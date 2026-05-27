# GrimbaNews — Tax / VAT Compliance Surrogate Plan

**Sprint ID:** S1269
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Tax / VAT compliance`
**Walk wave:** CCCC

## Gating dependency

Tax/VAT compliance needs:

- Stripe install (S1261) — Stripe Tax is the path of least resistance
- Per-customer VAT/Tax ID intake
- B2B reverse-charge handling per EU rules
- FR / EU / US / CA registration where revenue thresholds apply
- Accountant signoff on Stripe Tax config + VAT MOSS / OSS registration
- Invoice template that prints the correct legal entity + VAT line

## Surrogate-now infra

- **Stripe Tax** — automatic per-jurisdiction calculation if enabled at the account level
- **`grimba_advertiser_leads_sales_mailbox`** — proves the per-region routing pattern that would inform VAT region inference
- **`/legal/terms`** — Stellar-shipped legal surface; VAT clauses already templated

## Honest framing

Operator-side accounting pickup. Stripe Tax automates the *calculation*; the *registrations* are Iboga's accountant call (and per-jurisdiction filing) — that work doesn't live in the codebase.

## Owners

- **Finance:** Warren Buffett — Stripe Tax config + registration roadmap
- **Strategy:** Ray Dalio — per-region revenue threshold tracking
- **Legal:** TBD counsel — per-jurisdiction registration sign-off
- **Backend:** Rajesh Kumar — Stripe Tax wiring + invoice template
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1269 row)
- Stripe install: `docs/GRIMBANEWS_PAID_TIER_STRIPE_INSTALL_SCOPE.md`
- Invoice generation: `docs/GRIMBANEWS_API_INVOICE_GENERATION_SCOPE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
