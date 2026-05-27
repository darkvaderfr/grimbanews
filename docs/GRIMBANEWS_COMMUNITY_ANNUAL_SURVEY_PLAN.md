# GrimbaNews — Community Annual Survey Plan (Per-Reader Yearly Wrap)

**Sprint ID:** S2098
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — annual community survey`
**Walk wave:** BBBB

## Gating dependency

A community-wide annual survey needs:

- OSS community provisioning (S2043, deferred — no public OSS org)
- ≥1 year of contributors / readers cohort
- Survey tool (LimeSurvey self-hosted, Typeform, Tally)
- Per-locale survey copy
- Aggregation + public results publishing
- DPIA review (PII in free-text responses)

For a reader-side yearly wrap (per-reader reading-pattern recap), additionally needs:

- Per-reader feature vector (S1342, deferred)
- Reading-history capture (today only `grimba_read` cookie)
- Per-reader email-delivery (newsletter v2 band, partial)

## Surrogate-now infra

- **Newsletter recap** — annual editorial recap shipped via existing newsletter cadence is the de-facto "year-in-review" surrogate
- **`/methodologie` annual-update section** — when methodology evolves, the change-log is the implicit annual community update
- **Git log** — full transparency of every code change since S1

## Honest framing

The contributor-side survey gates on OSS org existing. The reader-side yearly-wrap is more achievable (newsletter-driven) once per-reader history capture (S1342) ships.

## Owners

- **CMO:** Gary Vaynerchuk — survey scope + brand fit
- **Community Mgr:** Maria Lopez — survey design + outreach
- **Product:** Liam Smith — reader-side wrap UX
- **Data Eng:** Benjamin Lee — aggregation
- **Compliance:** Maya Patel — DPIA
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2098 row)
- Per-reader feature vector: `docs/GRIMBANEWS_PER_READER_FEATURE_VECTOR_DESIGN.md`
- OSS methodology repo gate: `docs/GRIMBANEWS_OSS_METHODOLOGY_README_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
