# GrimbaNews — App Store Review Channel

**Status:** plan v0 (no App Store Connect or Google Play Console accounts; no review monitoring)
**Owner:** Emma Brown (Customer Success Lead) monitors reviews + Ethan Wilson (Support) replies + Steve Jobs (CPO) escalates trends + Larry/Ray on store-account billing
**Walks:** Mythos S1159 (App review channel) deferred → partial
**Gating dependency:** App Store Connect ($99/yr Apple Dev) + Google Play Console ($25 one-time) + native shell shipped + `boulabrice@gmail.com` linked as primary Apple ID

## Why this exists

S1159 is the inbound reader voice once a store listing exists. Today reader voice arrives only via `/contact` form + Twitter/X mentions — sparse. Store reviews + ratings change discovery (App Store SEO weighted) and product priority.

## Today's surrogate

- **`/contact` form** — `GrimbaContactController` writes to admin inbox.
- **Twitter/X mentions** — manual monitoring.
- **No store presence** — zero reviews to monitor.

## Review monitoring approach

| Source | Tool | Cadence | Owner |
|---|---|---|---|
| App Store Connect reviews | App Store Connect built-in + Sensor Tower (free tier) | daily | Emma Brown |
| Google Play Console reviews | Play Console built-in + AppFollow free tier | daily | Emma Brown |
| Slack feed | RSS-to-Slack webhook (per store) | hourly | Hannah Kim wires |

## Response SLA

| Rating | Initial response | Resolution |
|---|---|---|
| 1-2 star | within 24h | within 7 days |
| 3 star | within 48h | within 14 days |
| 4-5 star | within 7 days (thank you) | NA |

## Response playbook (Emma Brown + Ethan Wilson)

- **Never use external LLM provider names** — replies must say "NobuAI" if AI mentioned.
- **Acknowledge specifically** — quote one phrase the reviewer used.
- **Offer concrete next step** — link to support@grimbanews.com or `/contact`.
- **Never argue back** — accept the feedback as valid; promise to look into it.
- **Tag in CRM** — once a CRM exists; today: copy review text to a manual Google Doc tracker.

## Quality trend dashboard (deferred to S1418 author analytics → broader product analytics)

- Per-week avg rating trend.
- Per-version rating dip detection.
- Top 5 themes in 1-2 star reviews (manual classification).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1159)
- Sister docs: `docs/GRIMBANEWS_MOBILE_APP_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_APP_STORE_OPTIMIZATION_PLAN.md`, `docs/GRIMBANEWS_RETENTION_PLAYBOOK.md`
- Existing inbound: `app/Http/Controllers/GrimbaContactController.php`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
