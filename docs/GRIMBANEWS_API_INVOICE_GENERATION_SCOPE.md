# GrimbaNews — API Invoice Generation Surrogate Plan

**Sprint ID:** S1255
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API invoice generation`
**Walk wave:** CCCC

## Gating dependency

Per-customer invoice PDFs need:

- The billing meter from S1254
- Stripe installed (S1261) — Stripe-generated invoices are the path of least resistance
- Per-jurisdiction tax info (S1269 VAT)
- Customer billing address + business name + VAT/Tax ID intake form
- Invoice numbering policy compliant with FR/EU + US/CA tax rules

## Surrogate-now infra

- **Stripe Hosted Invoice Page** — out-of-the-box once Stripe ships; renders + emails the PDF
- **`/datasets/` exports** — internal pattern for daily CSV emission proves the cron-driven snapshot harness
- **`config/grimba_credits.php`** — internal cost ledger establishes the line-item shape

## Honest framing

Lowest-effort row in the billing cluster *if* S1254 + S1261 are in place — Stripe ships PDF + email out of the box. The only Grimba-side work is the line-item mapping and the operator policies (numbering + retention).

## Owners

- **Product:** Liam Smith — invoice template scope
- **Finance:** Warren Buffett — numbering + retention policy
- **Backend:** Rajesh Kumar — Stripe `subscription_items` mapping
- **Legal:** TBD counsel — per-jurisdiction template review
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1255 row)
- Billing meter: `docs/GRIMBANEWS_API_BILLING_METER_SCOPE.md`
- Tax/VAT compliance: S1269 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
