# GrimbaNews — Collaborative Filter (Reader-Similarity) Surrogate Design

**Sprint ID:** S1343
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — Collaborative filter (reader-similarity)`
**Walk wave:** BBBB

## Gating dependency

A reader-similarity collaborative filter needs:

- Per-reader feature vector (S1342, deferred — see sibling doc)
- A similarity-job offline pipeline (cosine on member_features, or matrix factorization)
- A `reader_similarity` table (`member_id`, `neighbor_id`, `score`)
- A ranker that pulls neighbor-read articles into the For-You rail
- Per-cohort A/B harness (S1346, deferred)

Today: zero of these. No `member_features` exists (S1342). Without features, similarity is undefined.

## Surrogate-now infra

The closest live signal of "people like you read this" is editorial:

- **Per-cluster `GrimbaDossierVoices`** — surfaces N sources per cluster, which functionally is "here are the other angles being read on this story"
- **Category landing** (`/categorie/{slug}`) — most-read-by-category proxy
- **`/le-flash` headline carousel** — global "top stories right now" without per-reader personalization

These are non-personalized signals; they do not implement collaborative filtering but they fill the same shelf in the reader UX.

## Honest framing

Collaborative filtering with cookie-only state would require fingerprinting (privacy non-starter) or a sustained logged-in cohort (we have one — `members` table — but no engagement features captured against it yet). The right path is: S1342 feature vector → S1343 similarity job → S1346 A/B harness. None ship until S1342 ships.

## Owners

- **Data Science:** David Chen — similarity algorithm + offline job
- **Data Eng:** Benjamin Lee — `reader_similarity` table + scheduled pipeline
- **Backend:** Rajesh Kumar — ranker integration + cache layer
- **Privacy:** Sara Chen + Maya Patel — DPIA + opt-out path
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1343 row)
- Feature-vector gate: `docs/GRIMBANEWS_PER_READER_FEATURE_VECTOR_DESIGN.md`
- A/B harness gate: `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
