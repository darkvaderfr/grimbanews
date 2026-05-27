# GrimbaNews — Reader Highlight Save Design

**Status:** plan v0
**Owner:** Nina Patel (Lead FE) + Alex Morgan (UI/UX)
**Walks:** Mythos S1372 (reader-side highlight — text selection → save) deferred → partial
**Gating dependency:** S1371 annotation surface + client-side selection-to-anchor library.

## Why this exists

The save action is the entry point to the annotation primitive. The interaction must feel native, fast (< 80ms popover), and respect mobile selection conventions.

## v1 client behavior

- `selectionchange` listener on article body.
- Debounced (60ms) to avoid popover-flicker on drag.
- Min selection 3 chars; max 1200.
- Popover positions above selection on desktop; pinned-bottom on mobile (avoids selection-handle conflict).
- Three actions: Surligner / Annoter / Citer (copy formatted citation).

## Save flow

```
1. Client serializes selection to {xpath, startOffset, endOffset, quoteText}
2. POST /api/internal/annotations
3. Server validates anchor against current article HTML
4. Returns annotation id
5. Client re-renders highlight in place
```

## Conflict handling

- If anchor fails server-side validation, fall back to quote-text-only annotation (still saved, not anchored).
- Edge: if user has not opted in to "Carnets" yet, prompt one-time onboarding.

## Mobile considerations

- iOS Safari: rely on native callout-bar custom action ("Annoter" added via Web Share API where possible; fallback to bottom popover).
- Android Chrome: bottom-sheet popover.
- Touch-and-hold timing tuned to OS native (don't override).

## Cross-references

Master plan: S1372. Sister: S1371 (annotation schema), S1376 (notebook), S1545 (public).
