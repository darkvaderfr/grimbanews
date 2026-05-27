# GrimbaNews — Web Push Delivery Worker Surrogate Plan

**Sprint ID:** S1304
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Web push delivery worker`
**Walk wave:** CCCC

## Gating dependency

Delivery worker needs S1302 (VAPID server) + S1303 (payload contract). Beyond that:

- Laravel queue worker dedicated to push (separate queue name to avoid blocking digest queue)
- Concurrency limit (provider per-endpoint limits)
- Backoff schedule (exponential with cap)
- Endpoint-pruning on 410 Gone
- Per-recipient frequency cap (avoid spamming when many alerts fire at once)

## Surrogate-now infra

- **`grimba:saved-search-digests`** — withoutOverlapping job pattern; same worker shape
- **`config/queue.php`** — Laravel queue config that the push queue would extend
- **`GrimbaProviderCredits` budget guard** — backoff inspiration for cost-aware workers (analogous to FCM/Mozilla rate limits)

## Honest framing

Standard async job pattern — ~3 days build once S1302 + S1303 are done. The frequency-cap policy is the only opinionated piece.

## Owners

- **Backend:** Rajesh Kumar — worker + queue config
- **Platform:** Hannah Kim — concurrency + monitoring
- **DevOps:** Jacob Lee — supervisor config for new queue
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1304 row)
- Web push server: `docs/GRIMBANEWS_WEB_PUSH_VAPID_SERVER_PLAN.md`
- Payload contract: `docs/GRIMBANEWS_WEB_PUSH_PAYLOAD_CONTRACT_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
