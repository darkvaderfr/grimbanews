# GrimbaNews — Blocked Sources UI Design

**Status:** plan v0 (no block-source primitive; readers cannot exclude a source from their feed)
**Owner:** Alex Morgan (UI/UX) + Nina Patel (Lead Frontend) + Liam Smith (PM) on copy
**Walks:** Mythos S1514 (Blocked sources — UI) deferred → partial
**Gating dependency:** Member auth + server persistence (S1515 sibling doc).

## Why this exists

S1514 lets a reader hide all articles from a specific source. Useful for: reader preference, paywalled source avoidance, low-trust source rejection. Today readers must scroll past — no opt-out.

## Today's surrogate

- None. All sources rendered to all readers in editorial position.

## UI surfaces

### On article card (hover / 3-dot menu)
```
  ⋯  Plus d'options
       ├── Sauvegarder
       ├── Partager
       ├── Bloquer cette source
       └── Signaler
```

### On `/source/{slug}` page (header)
```
  [logo] Le Figaro
         Centre-droit • France
         [ Bloquer cette source ] [ Suivre ]
```

### On `/account` page (manage blocks)
```
  Sources bloquées
  ─────────────────────────────────
  Le Figaro          [ Débloquer ]
  Sputnik            [ Débloquer ]
  ...
```

## Microcopy

- Confirm dialog: "Les articles de {source} ne seront plus affichés sur votre fil pour-vous, la home et les pages catégorie. Vous pouvez débloquer à tout moment depuis vos préférences."
- Anonymous-mode hint: "Pour bloquer définitivement, créez un compte. En mode invité, le blocage dure tant que vos cookies sont conservés."

## Filter application points

- `/pour-vous` — exclude blocked sources
- `/` home — exclude blocked sources from rails
- `/categorie/{slug}` — exclude
- `/dossier/{id}` cluster — KEEP visible (transparency principle: in cluster bias-distribution context, the reader should see who's covering it)
- `/search` results — exclude

## Accessibility

- Block action ARIA-labeled "Bloquer la source {name}"
- Confirmation dialog focus-trapped + escape-dismissable
- Color-blind safe (no red/green only)

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1514)
- Sister docs: `docs/GRIMBANEWS_BLOCKED_SOURCES_SERVER_PERSISTENCE_PLAN.md`, `docs/GRIMBANEWS_FOLLOWED_TOPICS_SERVER_PERSISTENCE_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
