# GrimbaNews — Per-Reader Feature Vector Surrogate Design

**Sprint ID:** S1342
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — Per-reader feature vector`
**Walk wave:** BBBB

## Gating dependency

A per-reader feature vector requires:

- `member_features` table (per-reader: read-categories, click-cluster IDs, dwell-time vector, refresh cadence, region preference)
- Capture beacon (POST per article view → feature-store-style append)
- Lazy backfill or batch job to derive feature deltas
- Privacy contract update on `/vie-privee` + per-category consent (S1862, deferred)
- For-You ranker that actually consumes the vector (S1501 ML feed band)

None of the above is shipped. Today the only per-reader state lives in cookies — `grimba_read` (set of read article IDs), `grimba_consent` (consent class), `grimba_lang`, `grimba_region`, `grimba_saved_categories`. There is no server-side member-features row.

## Surrogate-now infra

Cookie-only personalization that approximates a feature vector:

- **`grimba_read`** — set of seen article IDs (~30-day rolling)
- **`grimba_saved_categories`** — explicit reader-curated categories
- **`grimba_region`** — geo bucket for /local ranking
- **`grimba_lang`** — locale preference (FR/EN today, more post-S1101)
- **`app/Support/GrimbaForYou.php`** — reads these cookies + builds an in-request "for-you" rail without persisting

This is a **cookie-resident feature vector** — privacy-safe, reset on cookie wipe, no member-row required. It approximates a feature vector for the For-You rail without a `member_features` table.

## Why partial

The cookie approach is genuine personalization but caps at ~4 dimensions and resets on browser clear. A real feature vector enables collaborative filtering (S1343) and content-based filtering (S1344) — both deferred until the persistent feature store ships.

## Owners

- **Data Eng:** Benjamin Lee — `member_features` schema + ingest pipeline
- **Backend:** Rajesh Kumar — capture beacon + endpoint
- **Frontend:** Nina Patel — `<x-grimba-for-you>` partial wiring
- **Privacy:** Sara Chen (CISO) + Maya Patel (Compliance) — per-category consent + DPIA
- **Data Science:** David Chen — vector schema + ranker prototype
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1342 row)
- Collaborative-filter gate: `docs/GRIMBANEWS_COLLABORATIVE_FILTER_READER_SIMILARITY_DESIGN.md`
- Content-filter gate: `docs/GRIMBANEWS_CONTENT_BASED_FILTER_ARTICLE_SIMILARITY_DESIGN.md`
- For-You surrogate: `app/Support/GrimbaForYou.php`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
