# GrimbaNews — API SOC 2 / GDPR Data Export Surrogate Plan

**Sprint ID:** S1258
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API SOC2 / GDPR data export`
**Walk wave:** CCCC

## Gating dependency

A B2B-customer data-export endpoint needs:

- Per-customer scoping (gates on multi-tenant S1192-S1200)
- A DSAR workflow (subject access request) per GDPR Art. 15
- An export bundler (request log, key metadata, customer profile)
- Counsel-approved retention statement + signed DPA on file

## Surrogate-now infra

- **`coffre/export.csv`** — internal Vault export pattern for the reader-side GDPR right of access
- **`GrimbaVaultEvents` + `ip_hash`** — privacy-by-design pattern for event capture
- **`grimba:purge-stale` cron** — TTL enforcement pattern
- **`/legal/privacy` + `/legal/dpa`** — Stellar-shipped legal surfaces; DPA template lives there

## Honest framing

Operator-side legal + tooling pickup. The internal patterns (Vault CSV + IP hash + cron purge) prove the engineering is solvable; the gate is counsel + per-jurisdiction signoff, not code.

## Owners

- **CISO:** Sara Chen — SOC 2 control mapping
- **Security:** Maya Patel — DSAR runbook
- **Legal:** TBD counsel — DPA template + retention statement
- **Backend:** Rajesh Kumar — bundler job
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1258 row)
- Vault export pattern: `coffre/export.csv` flow
- Reader DSAR: `docs/GRIMBANEWS_PRIVACY_OPS_PER_LOCALE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
