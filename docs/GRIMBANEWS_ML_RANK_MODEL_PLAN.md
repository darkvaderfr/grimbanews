# GrimbaNews — ML Rank Model Surrogate Plan

**Sprint ID:** S1345
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — ML rank model (LR / GBDT / NN)`
**Walk wave:** CCCC

## Gating dependency

A learned ranking model (logistic regression / GBDT / neural) needs:

- Training data — click logs + engagement signals + dwell time (largely uninstrumented today)
- Per-reader feature vectors (S1501 ML feed deferred)
- Per-article feature vectors (categorical + numeric + embedding)
- Training pipeline (offline batch with shadow-eval before promotion)
- A/B harness (S1346) for promotion

## Surrogate-now infra

- **`GrimbaVaultEvents`** — privacy-safe event sink (ip_hash) provides minimal training corpus
- **Rule-based ranking today** — `GrimbaPostRanking` (if exists) and homepage cluster ordering use recency + bias-diversity + source-count
- **`docs/GRIMBANEWS_PER_READER_FEATURE_VECTOR_DESIGN.md`** — feature vector design doc

## Honest framing

Most ambitious ML work in the roadmap. Needs: instrumentation (3 months data collection), feature engineering (1 month), model training (1 month), A/B harness (1 month). Today's rule-based ranking is honestly good enough at current scale; ML rank earns its complexity at >1M MAU.

## Owners

- **Data Science:** David Chen — model architecture + training
- **Data Engineering:** Benjamin Lee — feature store + pipeline
- **Backend:** Rajesh Kumar — serving layer
- **Product:** Liam Smith — promotion criteria
- **Strategy:** Ray Dalio — ML cost vs uplift review
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1345 row)
- Feature vector design: `docs/GRIMBANEWS_PER_READER_FEATURE_VECTOR_DESIGN.md`
- A/B rank harness: S1346 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
