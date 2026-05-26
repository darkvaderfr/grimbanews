# GrimbaNews — Personalization v2 Launch Playbook

**Status:** playbook v0 (no ML rank; rule-based v1 ships today)
**Owner:** Steve Jobs (CPO) on UX + Liam Smith (PM) on rollout + Sara Chen (CISO) on privacy posture
**Walks:** Mythos S1350 (personalization v2 launch) deferred → partial
**Gating dependency:** Feature store (S1342 per-reader feature vector) + collaborative filter (S1343) + content-based filter (S1344) + ML rank model (S1345) + A/B harness (S1346 per `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`). Playbook itself is operator-side.

## Why this exists

S1350 was honest-deferred as gating on S1341-S1349. The launch sequence — how a personalization v2 rolls out without becoming a creepy filter bubble — is operator-side scope and **doesn't depend on ML infra**. This document sequences the launch so when ML infra lands the rollout is straight execution, with reader-trust guards built in.

## Today's v1 surface

- `/pour-vous` route ships a personalized rail per `views/pour-vous.blade.php`.
- **Personalization signal** = `grimba_for_you_recent` cookie (last N article IDs the reader has opened).
- **Rule-based rank**: rails fall back to homepage when cookie is empty (cold-start per S1347 partial).
- **Diversity guard**: `HomeFeedState` de-dupes by source-publisher across rails (S1348 partial).
- **No member-side feature vector** (per S1342 deferred).
- **No ML rank** (per S1345 deferred).
- **No collaborative filter** (per S1343 deferred).

## v2 scope

| v1 | v2 |
|---|---|
| Last N opened cookie | Per-reader feature vector (categories preferred, regions preferred, recency-weighted) |
| Homepage fallback | Personalized cold-start (region + locale signals) |
| Rule-based rank | ML rank (LR / GBDT) blended 60% with rule-based 40% |
| Single rail | Multiple personalized rails (more like this, related to your saves, regional follow-up) |
| Cookie-only signal | Cookie + saved-search opt-ins + vault saves as soft signal |

## Reader-trust guards (non-negotiable)

These are **launch-blocking** if they don't ship:

1. **Diversity floor.** No more than 2 consecutive cards from same source. Already-shipping per `HomeFeedState` source-publisher dedupe; extend to ML rank output.
2. **Bias spread floor.** Every personalized rail shows ≥1 left + ≥1 center + ≥1 right card when corpus permits (per `App\Support\GrimbaSourceBreakdown`).
3. **Region spread floor.** Personalized feed never collapses to single editorial_region.
4. **Personalization opt-out** (per S1349 ship). Single click → reverts to homepage rule-based rank.
5. **Explainability surface.** Each personalized card carries "Why am I seeing this?" tooltip → 1-line plain-language rationale.
6. **No-PII opt** for the embedding store (per `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md` privacy section).

## Launch phases

### Phase 0 — Prep

1. **Feature store live** (S1342). Per-reader vector behind `members.feature_vector_id` (gates on Sanctum-auth-bound members; cookie-only readers keep cookie-only model).
2. **ML rank model trained** (S1345). Offline-trained, online-served.
3. **Cold-start v2 designed** (S1347 ship). Region cookie + first-visit categories pick → seed rank.
4. **A/B harness live** (S1346 ships, gates on `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`).
5. **Per-rail "why" tooltip** designed.
6. **Personalization opt-out toggle** wired (S1349 ship).
7. **Privacy ROPA refresh** — `docs/GRIMBANEWS_GDPR_ROPA.md` adds member feature vector as processing activity.

### Phase 1 — Internal-only beta

1. **Feature flag** `personalization_v2_enabled` admin-only.
2. **Side-by-side comparison** — Lucy + Steve test 30 representative reader profiles (cookie state).
3. **Tuning** — blend weights, diversity floor strictness, region-spread floor.

### Phase 2 — 5% canary

1. **5% cookie-pinned cohort** to v2.
2. **Per-variant analytics** — CTR, session-length, return-visit rate, opt-out rate.
3. **Rollback gates:**
   - CTR drop > 10% vs v1 → rollback.
   - Opt-out rate > 8% → rollback.
   - Diversity floor breach > 1% of impressions → rollback.
4. **4-week observe window** (personalization needs longer-than-search to settle).

### Phase 3 — 25% canary

1. **Scale to 25%** if Phase 2 passes.
2. **4-week observe window.**
3. **Same rollback gates.**

### Phase 4 — Full launch

1. **100% on v2.**
2. **Feature flag retained 180 days** (personalization changes have longer detection horizon than search).
3. **Public announcement** — single brief on `/blog` (gates on blog deferred). Tone: "We're adding smarter personalization with these reader-trust guards built in." Not: "We've cracked the algorithm."

## What we will NOT ship

- **Behavioral retargeting across sessions** — opt-in only; default-off. Today we don't even have ad-network behavioral signals (per `docs/GRIMBANEWS_HEADER_BIDDING_PLAN.md` non-consent path).
- **Per-reader email re-targeting** — vault digest + saved-search digest remain user-initiated, not algorithmic.
- **Engagement-maximizing rank** — we won't optimize for time-on-site as primary objective. Optimize for "did the reader find what they were looking for" + "did they save it" (proxy for value, not stickiness).

## Privacy posture

- **Feature vector lives only on member rows** — never on cookie-only readers.
- **Member feature vector is encrypted at rest** (Laravel `Crypt::` cast).
- **Reader can request feature vector reset** via GDPR data-export / erasure (per `docs/GRIMBANEWS_GDPR_ROPA.md`).
- **Feature vector excluded from CSV exports** (per `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md`).
- **Ombudsman authority to audit feature-vector behavior** per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` section 4.

## Observability

- **Per-rail diversity-floor monitor** — flag if any rail violates floor > 1% of impressions.
- **Per-variant bias-spread monitor** — flag if v2 collapses bias spread vs v1.
- **Personalization opt-out rate** — flag if rising.
- **CTR + return-visit per variant.**

## Engineering effort estimate

- Feature store (S1342): ~6 sprints.
- ML rank model + serving (S1345): ~8 sprints.
- Collaborative + content-based filters (S1343, S1344): ~4 sprints.
- A/B harness (S1346, gates on `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`): per that doc.
- Personalization opt-out + "Why am I seeing this?": ~3 sprints.
- Observability + dashboards: ~2 sprints.
- **Full ship to Phase 4: ~25-30 sprints from Phase 0.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1350; gates on S1341-S1349)
- Sister docs: `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`, `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`, `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md`
- Existing surface: `platform/themes/echo/views/pour-vous.blade.php`, `app/Support/HomeFeedState.php`
- Source-breakdown helper: `app/Support/GrimbaSourceBreakdown.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
