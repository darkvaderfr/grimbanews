# GrimbaNews — Highlight UI Design

**Status:** plan v0 (no JS handler for text-selection highlighting)
**Owner:** Alex Morgan (UI/UX) + Nina Patel (Lead Frontend) + Liam Smith (PM)
**Walks:** Mythos S1542 (Highlight UI — text selection) deferred → partial
**Gating dependency:** Annotation schema (S1541 sibling) shipped + member auth.

## Why this exists

S1542 is the reader-facing surface. The schema (S1541) is meaningless without the UI to create/view/manage highlights.

## Interaction model

```
Reader selects text in article body
  ──► floating popover appears near selection:
        [ ⭐ Surligner ]  [ 💬 Ajouter une note ]  [ 🔗 Partager ]
  ──► on "Surligner" click:
        - selection wrapped in <mark class="grimba-highlight" data-highlight-id="..."> 
        - server POST /api/highlights with anchor + visibility=private
        - confirmation toast: "Passage surligné · Sauvegardé dans votre coffre"
```

## Highlight rendering

- Persistent on subsequent visits: rendered server-side or hydrated via SPA fetch on load.
- Color: amber (single-color v1; per-tag colors deferred to v2).
- Hover: shows note + reactions count (if any).
- Click: opens highlight detail modal.

## Note attachment

- Note input: max 2,000 chars (Markdown subset: bold, italic, link).
- Auto-save draft to localStorage; commit on close.
- Edit + delete from highlight detail modal.

## Mobile UX

- Long-press to start selection (iOS/Android native).
- Popover renders above keyboard if note input opened.

## Accessibility

- Keyboard alternative: enter "highlight mode" via shortcut (e.g., Alt+H), arrow keys extend selection.
- Screen reader: `<mark aria-label="Passage surligné, note: {note excerpt}">`.

## Privacy default

- Default visibility: private.
- Sharing requires explicit second action (one-tap toggle in detail modal).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1542)
- Sister docs: `docs/GRIMBANEWS_ANNOTATION_SCHEMA.md`, `docs/GRIMBANEWS_PRIVATE_ANNOTATIONS_SYNC_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
