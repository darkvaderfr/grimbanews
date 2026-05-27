# GrimbaNews — Methodology Video Transcript (Accessibility)

**Status:** plan v0
**Owner:** Michael O'Connor (Technical Writer) + Lucy Leai (Strategy)
**Walks:** Mythos S1795 (methodology video transcript) deferred → partial
**Gating dependency:** S1791 video recording.

## Why this exists

WCAG 2.1 AA accessibility requires video transcripts. Plus: text-searchable transcripts boost SEO + serve readers who prefer text.

## v1 deliverables

- Per-locale full transcript (FR, EN, DE, ES, PT-BR).
- Per-transcript SRT file (subtitles).
- Per-transcript per-section anchor (deep-link to section).
- Per-transcript published below video on `/methodologie`.

## Production cadence

1. Per-locale automated transcript via Whisper or equivalent (operator-side).
2. Per-locale human review + correction.
3. Per-locale operator publishes.

## Per-transcript SEO benefit

- Transcripts indexable by Google.
- Per-section anchors deep-linkable.
- Per-transcript schema.org VideoObject + transcript JSON-LD.

## Per-transcript update cadence

- Whenever video updated, transcript re-generated + re-reviewed.
- Per-update changelog noted on `/methodologie#api` page.

## Cross-references

Master plan: S1795. Sister: `docs/GRIMBANEWS_METHODOLOGY_VIDEO_PRODUCTION_PLAN.md`, `docs/GRIMBANEWS_FONT_SCALING_A11Y_MATRIX.md` (Wave LLL).
