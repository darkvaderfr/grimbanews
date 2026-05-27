# GrimbaNews — French-Canadian Dialect Detection Plan

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Lucy Leai (Strategy)
**Walks:** Mythos S1624 (FR-CA dialect detection) deferred → partial
**Gating dependency:** Extension of `GrimbaLanguageDetector` to discriminate FR-CA from FR-FR.

## Why this exists

Today `posts.original_language='fr'` is single bucket. FR-CA (Quebec) has distinct vocabulary (poutine, chum, blé d'inde), spelling (often closer to anglicism), and editorial register. Discrimination helps:
- Per-Quebec-locale UX
- Per-FR-CA editorial cadence
- Per-FR-CA source roster (Le Devoir, La Presse, Radio-Canada)

## v1 detection heuristic

Per-article extension to `GrimbaLanguageDetector`:
- Score articles for FR-CA markers (vocabulary list of ~50 dialectism words).
- Score for FR-FR markers (anglicism patterns common in Hexagone press).
- Tie-break: source country (Canada → likely FR-CA).
- Final label: `posts.original_language IN ('fr', 'fr-ca')`.

## v2 ML detection

NLP model trained on:
- Le Devoir / La Presse / Radio-Canada corpus (FR-CA labeled)
- Le Monde / Le Figaro / Libération corpus (FR-FR labeled)
- Per-article classifier confidence threshold.

## Per-FR-CA reader UX

- `/region/quebec` or `/canada-francophone` regional surface.
- Per-FR-CA newsletter cadence.
- Per-FR-CA editor.
- Per-FR-CA cookie-consent + legal pages (Quebec law differs).

## Cross-references

Master plan: S1624. Sister: `docs/GRIMBANEWS_AFRICA_FRANCOPHONE_EDITORIAL_CADENCE.md`, `docs/GRIMBANEWS_PER_LOCALE_LEGAL_PAGES_SETS.md`.
Code: `app/Support/GrimbaLanguageDetector.php`.
