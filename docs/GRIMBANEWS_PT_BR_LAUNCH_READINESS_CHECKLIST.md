# GrimbaNews — Brazilian Portuguese (PT-BR) Launch Readiness Checklist

**Status:** checklist v0 (per-locale ops gate; gates on S1111-S1119 shipping)
**Owner:** Lucy Leai (CEO) on go/no-go + Liam Smith (PM) on coordination + Sara Kim (QA) on smoke sign-off
**Walks:** Mythos S1120 (PT-BR launch readiness) deferred → partial
**Gating dependency:** All S1111-S1119 rows shipped first. PT-BR-native reviewer + Comms approval required.

## Why this exists

S1120 mirrors S1110 (ES). Checklist itself is operator-side.

## T-14 / T-7 / T-1 / T-0 / T+7 checklists

Identical structure to ES launch readiness checklist; substitute `pt-BR` for `es` and PT-BR-native reviewer for ES-native reviewer.

## PT-BR-specific additions

- Brazilian press list (Folha, Estadão, O Globo, BBC Brasil, DW Brasil) for outreach
- Lusofone-Africa press list (Angola: Jornal de Angola; Moçambique: Jornal Notícias; Cabo Verde: A Semana)
- Brazilian Google Search Console property (subdomain or path prefix)
- Plausible / GA filter for PT-BR-routed traffic

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1120 row)
- Sister docs: `docs/GRIMBANEWS_PT_BR_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_ES_LAUNCH_READINESS_CHECKLIST.md`, `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md`
- Existing infrastructure: same as sister docs
