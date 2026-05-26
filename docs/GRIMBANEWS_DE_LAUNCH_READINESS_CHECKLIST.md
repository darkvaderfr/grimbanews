# GrimbaNews — German (DE) Launch Readiness Checklist

**Status:** checklist v0 (per-locale ops gate)
**Owner:** Lucy Leai (CEO) + Liam Smith (PM) + Sara Kim (QA)
**Walks:** Mythos S1130 (DE launch readiness) deferred → partial
**Gating dependency:** All S1121-S1129 rows shipped first. DE-native reviewer + Comms approval required.

## T-14 / T-7 / T-1 / T-0 / T+7

Identical structure to ES launch readiness; substitute `de`.

## DE-specific additions

- DACH press list (Süddeutsche, FAZ, Zeit, NZZ, Standard, DW, Tagesspiegel, Spiegel)
- DE-specific GDPR / data-protection compliance audit (DE audience expects highest-bar privacy posture)
- Notice + Take-Down (NTD) response procedure honoring NetzDG (German Network Enforcement Act) — counsel review required before launch (Maya Patel + counsel; counsel external)
- DE Google Search Console subproperty for `/de/*`

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1130 row)
- Sister docs: `docs/GRIMBANEWS_DE_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_ES_LAUNCH_READINESS_CHECKLIST.md`, `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md`
- Existing infrastructure: same as sister docs
