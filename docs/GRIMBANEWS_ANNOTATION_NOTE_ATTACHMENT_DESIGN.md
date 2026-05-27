# GrimbaNews — Annotation Note Attachment Design

**Status:** plan v0
**Owner:** Nina Patel (Lead FE) + Alex Morgan (UI/UX)
**Walks:** Mythos S1543 (note attached to highlight) deferred → partial
**Gating dependency:** S1371 annotation surface + S1372 reader-side highlight save.

## Why this exists

A highlight without a note is a passive bookmark. The note attachment is what turns a passage into a reader's working idea — and the input pattern matters: too prominent and reader skips it; too hidden and reader never adds notes.

## v1 design

- After highlight save, popover offers "Ajouter une note" (secondary action).
- Note composer: small inline textarea (3 rows), markdown subset (bold, italic, list, link).
- 800-char soft cap with character counter.
- Auto-save on blur + every 5s while typing.
- Cancel = discard with confirm if > 30 chars.

## Storage

- Reuses `annotations.note` column from S1371 schema.
- Empty note allowed (highlight-only).

## UX defaults

- Empty-note highlights render as plain background overlay.
- Notes-present highlights render with subtle indicator (small triangle).
- Hover/long-press shows note inline.

## Cross-references

Master plan: S1543. Sister: S1371 (annotation), S1372 (highlight save), S1545 (public visibility), S1547 (export).
