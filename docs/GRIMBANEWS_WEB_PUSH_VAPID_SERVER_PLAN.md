# GrimbaNews — Web Push Server (VAPID) Surrogate Plan

**Sprint ID:** S1302
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Web push server (VAPID)`
**Walk wave:** CCCC

## Gating dependency

A VAPID push server needs:

- `composer require minishlink/web-push` (or equivalent)
- VAPID key pair generation + secure storage
- `webpush_subscriptions` migration
- A worker job that signs + sends payloads to FCM / Mozilla autopush / Apple gateways
- Retry-with-backoff + endpoint-pruning on 410 Gone

## Surrogate-now infra

- **`grimba:saved-search-digests` cron job** — proves the per-subscriber fan-out pattern works at scale
- **`grimba:nobuai-summaries` cron** — proves backoff + retry job pattern (cost-aware)
- **`config/grimba_credits.php`** — pattern for per-endpoint provider config

## Honest framing

Same gate as S1301; the *server* and the *opt-in* ship together because neither is useful alone. ~1 week build once green-lit.

## Owners

- **Backend:** Rajesh Kumar — package install + worker
- **DevOps:** Jacob Lee — secret storage + key rotation policy
- **Platform:** Hannah Kim — retry/backoff config
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1302 row)
- Web push opt-in: `docs/GRIMBANEWS_WEB_PUSH_OPT_IN_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
