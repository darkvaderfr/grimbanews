# GrimbaNews — Spelling Correction Surrogate Plan

**Sprint ID:** S1337
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Spelling correction`
**Walk wave:** CCCC

## Gating dependency

Search spelling correction needs:

- Per-locale dictionary (FR aspell + EN aspell + ES + DE if added)
- A correction prompt UX ("Did you mean: …?")
- A click-to-search behavior (autocorrect vs suggest)
- Per-locale awareness (don't suggest "color" → "colour" on FR)

Options: hunspell / aspell binaries, Levenshtein over indexed terms, or NobuAI driver call.

## Surrogate-now infra

- **`app/Support/GrimbaLanguageDetector`** — per-locale infra ready (S1028) — locale-aware corrections feasible
- **`GrimbaSavedSearches::matchingPosts()`** — search backend ready to accept corrected term
- **`tests/Unit/GrimbaLanguageDetectorTest`** — 26-test surface that proves per-locale routing

## Honest framing

Cheap to ship (aspell + Levenshtein is ~3 days). Decision-heavy on UX (autocorrect risks frustrating users who *meant* the rare spelling).

## Owners

- **Product:** Liam Smith — UX policy (suggest vs autocorrect)
- **Data:** David Chen — correction model
- **i18n:** Nina Patel — per-locale dictionary curation
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1337 row)
- Language detector: `app/Support/GrimbaLanguageDetector.php`
- Search typeahead: S1338 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
