# GrimbaNews — ML Feed Design Doc

**Status:** plan v0 (cookie-only pour-vous is the substrate; no server-side ML)
**Owner:** Elon Musk (CTO) on architecture + David Chen on model + Sara Chen (CISO) on privacy posture
**Walks:** Mythos S1501 (ML feed — design doc) deferred → partial
**Gating dependency:** Embedding store (S1076), reader interaction signals (none today), Ray cost approval.

## Why this exists

S1501 is the design doc for personalized feed ranking. Today the `/pour-vous` page is cookie-only (region + saved categories) — no model, no server-side reader graph. This is intentional privacy-first posture; the ML feed must preserve that posture as a default-off opt-in.

## Today's surrogate

- **`/pour-vous`** reads `pour_vous` cookie (region + categories) and filters the latest feed accordingly.
- No per-member behavior tracking server-side.

## Architecture (target)

```
reader signals (opt-in only):
  - saved categories (existing cookie)
  - region (existing cookie)
  - read articles (NEW — opt-in event log)
  - bookmarked / vault (existing for members)
  - skipped (NEW — explicit reader signal "not interested")

  ──► [feature vector per reader]
  ──► [ranker: lexical relevance × freshness × diversity × per-reader fit]
  ──► [bias-diversity guard: must include ≥1 source per major bias bucket]
  ──► [final ranked feed → /pour-vous]
```

## Privacy posture (Sara Chen — non-negotiable)

- **Opt-in only** — default to today's cookie-only mode.
- **Reader-visible toggle:** "Personalize my feed using my reading history" (off by default).
- **Anonymized server log** — sessions hashed; user_id NOT linked unless paid-tier (S1261).
- **30-day retention** of reader signal rows; aggregate-only thereafter.
- **One-click "forget my feed"** — clears all reader signal rows.

## Diversity guard

- Hard requirement: no bias bucket > 60% of feed.
- Hard requirement: ≥3 distinct sources per session.
- Soft requirement: surface ≥1 contrarian source if reader has clear bias lean (per S1538 echo-chamber detection — itself deferred).

## Fallback when no signal

- Empty profile → today's editor-picked + region-aware cookie behavior.
- Sparse profile (<5 reads) → blend 80% editor / 20% personalized.

## Acceptance gates

- Diversity guard never triggers (model respects it intrinsically).
- Reader-survey "I'm getting only one viewpoint" rate flat or improves vs baseline.
- Opt-in rate ≥10% within 90 days (otherwise feature is dead-weight).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1501)
- Sister docs: `docs/GRIMBANEWS_ML_FEED_COLLABORATIVE_FILTER_PLAN.md`, `docs/GRIMBANEWS_ML_FEED_AB_HARNESS_PLAN.md`, `docs/GRIMBANEWS_ML_FEED_LAUNCH_RETRO_PLAN.md`, `docs/GRIMBANEWS_FOLLOWED_TOPICS_SERVER_PERSISTENCE_PLAN.md`
- Existing infra: `/pour-vous` route + `pour_vous` cookie
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
