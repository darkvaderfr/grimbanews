# GrimbaNews — Per-Region Daily Digest Cadence

**Status:** cadence v0 (no per-region digest template; vault + saved-search digests are the per-member surrogate)
**Owner:** Lucy Leai (Strategy) on cadence + Steve Jobs (CPO) on template + Liam Smith (PM) on rollout
**Walks:** Mythos S1581 (per-region daily digest) deferred → partial
**Gating dependency:** Per-edition newsletter template + general-audience newsletter subscription surface beyond per-saved-search. Cadence design itself is operator-side.

## Why this exists

S1581 was honest-deferred: newsletter routing infrastructure exists per the `grimba_advertiser_leads_sales_mailbox` per-region mailbox pattern, but **no per-region daily-digest template** ships. The per-member surrogate (vault digest + saved-search digest) is per-individual, not per-region-edition. This document defines the cadence + template so when the template ships, send-cadence is settled.

## Today's mail infrastructure

- `App\Mail\GrimbaVaultDigestMail` — weekly per-member vault digest (Friday). Locked by `VaultDigestMailTest`.
- `App\Mail\GrimbaSavedSearchDigestMail` — weekly Monday 04:55 per-segment match digest. Locked by `SavedSearchAlertsTest`.
- `App\Mail\GrimbaAdvertiserLeadNotification` — per-region routing to `grimba_advertiser_leads_sales_mailbox` settings (FR/EN per `editorial_region`).
- LeafRelay (Acelle) is the delivery vendor — Iboga-owned VPS.

**No per-region daily digest** today.

## Proposed per-region cadence

| Region | Send time (local) | Send time (UTC) | Languages | Rationale |
|---|---|---|---|---|
| Africa francophone | 06:00 WAT (Lagos / Dakar / Abidjan) | 05:00 UTC | FR | Pre-commute morning read |
| Africa anglophone | 06:00 EAT (Nairobi / Kampala) | 03:00 UTC | EN | Same |
| Europe FR | 07:00 CET / CEST | 05:00/06:00 UTC | FR | Pre-commute Paris / Brussels / Geneva |
| Europe EN | 07:00 BST / GMT | 06:00/07:00 UTC | EN | Pre-commute London / Dublin |
| North America FR (Quebec) | 06:30 EST/EDT | 11:30/10:30 UTC | FR | Pre-commute Montreal |
| North America EN | 07:00 ET | 12:00/11:00 UTC | EN | Pre-commute Eastern US |
| Caribbean | 06:30 AST | 10:30 UTC | FR / EN | Per-territory pickup |
| DOM-TOM | varies | 06:00 local-time bucket | FR | Per-territory per `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md` |

Single-send-time-per-region (not per-subscriber). Operator simplicity > minor optimization. Per-subscriber send-time A/B is deferred per S1589.

## Send-cadence rules

- **Frequency:** daily (skipping weekends initially; weekend digest tier deferred to "weekend recap" variant).
- **Send window:** 30-minute spread to flatten LeafRelay load (start at slot, complete within 30min).
- **Time zones honored by region** — not by per-subscriber timezone (gates on per-subscriber timezone capture, deferred).
- **Holidays per-region** — skip per-region holiday list (operator-curated, lands in editorial calendar per `docs/GRIMBANEWS_EDITORIAL_CALENDAR_PLAN.md`).

## Subscription model

- **Default opt-out** — subscriber must opt-in.
- **Per-region selection** — subscriber picks region(s); can subscribe to multiple (e.g., Africa-FR + Europe-FR).
- **Language fallback** — if subscriber picks Europe-EN but article is FR-only, surface with NobuTranslator auto-translation note.
- **Single subscription form** at `/newsletter` (gates on general-audience newsletter ship — today only per-saved-search ships).

## Template structure

Each per-region digest is a Blade template (e.g., `resources/views/emails/daily-digest/africa-fr.blade.php`) extending a base.

**Sections:**

1. **Header** — GrimbaNews wordmark + region label + date.
2. **Editor's note** (optional, 1-2 sentences) — gates on in-house editorial roster.
3. **Top 5 stories** — top clusters from `App\Support\HomeFeedState` filtered by `editorial_region`.
4. **Bias-bar spotlight** — single cluster with cross-source representation (per `App\Support\GrimbaSourceBreakdown`).
5. **Middle Ground card** — per Wave CCCCCCCCCCC-KKKKKKKKKKK Middle Ground feature; surfaces a centrist-clustered story for the region.
6. **Local pickup** — 3 per-region stories from `editorial_region` + city-pool match.
7. **Yesterday's most-saved** — by region (gates on per-region vault analytics).
8. **Sponsor slot** (when newsletter monetization ships per `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`) — capped per inventory.
9. **Footer** — unsubscribe link + region change + delivery preferences.

## Per-template locale enforcement

- **FR templates use FR strings throughout.**
- **EN templates use EN strings throughout.**
- **Mixed-locale (e.g., FR article in EN digest)** — article rendered in original language with "Read in English" link via translate route.

Locked by pattern from Wave BBBBBBBBBBB (locale-pin mail commands).

## Pipeline

```
0500 UTC  -> grimba:fetch-rss + grimba:cluster-stories (existing crons)
0530 UTC  -> grimba:daily-digest --region=africa-fr  [new]
0600 UTC  -> grimba:daily-digest --region=europe-fr  [new]
0610 UTC  -> grimba:daily-digest --region=europe-en  [new]
... etc.
```

`App\Console\Commands\GrimbaDailyDigest` (new) takes `--region` arg, resolves subscriber list, renders per-region template, dispatches via LeafRelay.

## Bounce + unsubscribe handling

- Bounces handled by LeafRelay (Acelle) feedback loop.
- Unsubscribe link reads `newsletter_subscriptions.unsubscribed_at` per existing Botble member pattern.
- Per-region unsubscribe option in footer (per region or all).
- GDPR data-export honored per `docs/GRIMBANEWS_GDPR_ROPA.md`.

## Observability

- **Per-region send count** — logged to `grimba_automation_runs` with `job_name='daily_digest_send_{region}'`.
- **Per-region delivery rate** — pulled from LeafRelay weekly; surfaced on cockpit board.
- **Per-region open / click rate** — gates on tracking pixel + link-rewriter (S1286 deferred). Without those, opt-out signal is the only health metric.
- **Send failures > 5%** — paged per `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md` P2.

## CAN-SPAM + GDPR compliance

- Footer carries: physical address, unsubscribe link, sender identification.
- Sender domain matches operator brand (`@grimbanews.com`).
- DKIM + SPF + DMARC per `docs/GRIMBANEWS_GDPR_ROPA.md` mail-infra section.

## Engineering effort estimate

- General-audience newsletter subscription surface (`/newsletter`): 2 sprints.
- Per-region template scaffold + base: 2 sprints.
- Per-region template build-out (1 sprint each × 6 regions × 2 langs): ~6 sprints can batch.
- `grimba:daily-digest` command + scheduler: 1 sprint.
- Subscription preferences page: 2 sprints.
- Per-region observability: 1 sprint.
- Tests + e2e mailpit pass: 2 sprints.
- **Full ship: ~14-15 sprints.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1581; sister S1582-S1590)
- Sister docs: `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`, `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md`, `docs/GRIMBANEWS_EDITORIAL_CALENDAR_PLAN.md`, `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`
- Mail surfaces: `app/Mail/GrimbaVaultDigestMail.php`, `app/Mail/GrimbaSavedSearchDigestMail.php`, `app/Mail/GrimbaAdvertiserLeadNotification.php`
- Home rails feed: `app/Support/HomeFeedState.php`
- Source-breakdown: `app/Support/GrimbaSourceBreakdown.php`
- Locale enforce: `app/Http/Middleware/GrimbaLocaleEnforce.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
