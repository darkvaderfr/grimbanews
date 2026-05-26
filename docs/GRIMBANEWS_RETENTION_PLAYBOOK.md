# GrimbaNews — Retention Playbook

**Status:** playbook v0 (retention dashboard partial; reader-retention playbook deferred)
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM) + Steve Jobs (CPO) on UX nudges
**Walks:** Mythos S1300 (retention playbook) deferred → partial (advances existing partial)
**Gating dependency:** Retention cohort dashboard (S1299 partial → ship) + dormant detection (per `docs/GRIMBANEWS_RE_ENGAGEMENT_EMAIL_DESIGN.md`). Playbook itself is operator-side.

## Why this exists

S1300 was honest-deferred: "docs/GRIMBANEWS_ADVERTISER_CULTURE_FRESHNESS_PLAN.md covers freshness; reader-retention playbook deferred." Freshness is the ingest-side input; **retention** is the reader-side output. This document is the playbook — what we measure, what we nudge, what we never do.

## What retention means here

For GrimbaNews, retention = **reader returns to the site / opens our digest after first visit, sustained over weeks**.

We're not optimizing for engagement-maximization (per `docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md` "What we will NOT ship"). We're optimizing for **trust-building return** — readers come back because we provide value, not because we manipulate notification cadence.

## Cohort definitions

| Cohort | Definition |
|---|---|
| W0 visitors | First-ever visit in current week |
| W1 returning | W0 visitors who returned within 7 days |
| W4 returning | W0 visitors who returned in week 2/3/4 |
| W12 returning | W0 visitors who returned in week 5-12 |
| Newsletter subscribed | Visitors who subscribed within first 4 weeks |
| Vault user | Visitors who used vault save within first 4 weeks |
| Active member | Logged-in member with activity in last 30 days |
| Dormant member | Member with no activity in 30+ days |

Cohort definitions implementable via `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md` rollups.

## Target retention rates

| Metric | Target (mature state) | Current state |
|---|---|---|
| W1 return rate | 25% | unmeasured |
| W4 return rate | 15% | unmeasured |
| W12 return rate | 8% | unmeasured |
| Newsletter subscribe rate (first 4w) | 5% | unmeasured |
| Vault use rate (first 4w) | 3% | unmeasured |
| Active member % of total members | 65% | unmeasured |
| Member 1-year retention | 50% | unmeasured |

Measurement gates on warehouse rollups (per `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`).

## Nudges that pull retention (in order of effectiveness, hypothesis)

1. **Saved-search digest** (highest value per `App\Mail\GrimbaSavedSearchDigestMail` — already complete). Reader sets up alerts → gets weekly relevance → returns when story lands.
2. **Per-region daily digest** (per `docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md`) — gives readers a habit-forming morning slot.
3. **Vault** (`/coffre` — already ships) — reader builds an investment, returns to consume.
4. **Reading mode + preferences** (per `docs/GRIMBANEWS_READING_MODE_DESIGN.md`) — readers who personalize stay.
5. **Personalization v2** (per `docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md`) — once tuned with guards.
6. **Bias-bar tutorial overlay** (per `docs/GRIMBANEWS_BIAS_BAR_TUTORIAL_OVERLAY_DESIGN.md`) — readers who understand the bar are more likely to value the product.
7. **Re-engagement email** (per `docs/GRIMBANEWS_RE_ENGAGEMENT_EMAIL_DESIGN.md`) — capped 3-step cadence.

## Anti-patterns we won't ship

- **Daily streak counter** (S1291 deferred — explicitly NOT shipping). Anti-pattern: turns reading-news into game-mechanic; pressures readers into engagement disconnected from value.
- **Streak email reminders** (S1292 deferred — same).
- **Reader achievement badges** (S1296 deferred — same).
- **High-frequency push notifications** (per `docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md` "What we will NOT ship").
- **Behavioral retargeting across sessions** (same).
- **Engagement-maximizing rank** (same).
- **Time-on-site as primary objective** (same).

This list is **non-negotiable** per Vader directive. Adding any item requires Vader sign-off + Lucy-led editorial review.

## Annual review cadence

Lucy + Steve + Liam quarterly review:
- Per-cohort retention rates.
- Per-nudge attribution (gates on A/B harness per `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`).
- Anti-pattern guard: any creeping behavior that resembles the anti-pattern list?
- Reader feedback themes from `/contact`.
- Ombudsman complaints (per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` section 4 + 6).

## Per-region retention considerations

- **Africa francophone:** higher mobile-first reader share; reading-mode + offline-mode (per S1561+ partial) more important than desktop personalization.
- **Europe FR:** newsletter cadence-sensitive (commuter morning).
- **DOM-TOM:** small-population; relationship-based retention more than algorithmic.
- **Diaspora EN:** time-zone-aware send (per `docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md`).

## Ethical guard

We're a news-bias product. The retention metrics should track **reader value**, not addiction-shaped engagement. Lucy is the explicit owner of this guard. If a metric or feature starts pressing the addiction axis, she flags + we revisit.

## Engineering effort estimate (already covered in sister docs)

Per-feature effort lives in the sister docs:
- Re-engagement email: ~12 sprints per `docs/GRIMBANEWS_RE_ENGAGEMENT_EMAIL_DESIGN.md`.
- Per-region digest: ~15 sprints per `docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md`.
- Reading mode: ~8 sprints per `docs/GRIMBANEWS_READING_MODE_DESIGN.md`.
- Personalization v2: ~25-30 sprints per `docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md`.
- Bias-bar tutorial overlay: ~6 sprints per `docs/GRIMBANEWS_BIAS_BAR_TUTORIAL_OVERLAY_DESIGN.md`.

This playbook is a coordination doc, not a feature spec.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1300; sister S1291-S1300, related S1299, S1297, S1298)
- Sister docs: `docs/GRIMBANEWS_RE_ENGAGEMENT_EMAIL_DESIGN.md`, `docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md`, `docs/GRIMBANEWS_READING_MODE_DESIGN.md`, `docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_BIAS_BAR_TUTORIAL_OVERLAY_DESIGN.md`, `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`, `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`, `docs/GRIMBANEWS_ADVERTISER_CULTURE_FRESHNESS_PLAN.md`, `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`
- Existing vault: `app/Support/GrimbaVault.php`, `/coffre`
- Existing saved-search digest: `app/Mail/GrimbaSavedSearchDigestMail.php`, `app/Support/GrimbaSavedSearches.php`
- Existing vault analytics: `/admin/grimba/vault-analytics`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
