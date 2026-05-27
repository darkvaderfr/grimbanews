# GrimbaNews — Public Annotations Design

**Status:** plan v0
**Owner:** Alex Morgan (UI/UX) + Liam Smith (PM) + Maya Patel (Compliance — moderation)
**Walks:** Mythos S1545 (highlight visible to other readers / public annotations) deferred → partial
**Gating dependency:** S1541 reader-side highlights + moderation primitive (S1549) + visibility opt-in.

## Why this exists

Public annotations can elevate articles — turning a passage into a discussion node — but the moderation cost is high. v1 ships with conservative defaults: private is the default, public requires deliberate opt-in, and editorial team can hide any public annotation.

## v1 design

- Per-annotation visibility set at create-time: `private` (default) / `shared-link` / `public`.
- Public annotations show as subtle dotted underline on article; tap reveals reader handle + note.
- Per-article counter of public annotations (small badge, not number-driven).
- Annotation author opt-in required for handle display (anonymized "Lecteur" otherwise).

## Moderation

- Reader can flag any public annotation (`/api/annotations/flag`).
- Flag threshold auto-hides for editorial review.
- Editorial actions: hide / warn-author / remove / reinstate.
- Repeat-offender auto-throttle on public-annotation rights.

## Privacy

- Author handle never shown without opt-in.
- IP truncation; no per-reader public-annotation log.
- Moderation events written to per-author trust log.

## Anti-patterns

- No default-public.
- No reactions/likes on annotations (gamification escalates moderation cost).
- No follow-annotator surface in v1.

## Cross-references

Master plan: S1545. Sister: S1541 (highlights), S1543 (note attachment), S1549 (moderation), S1547 (export).
