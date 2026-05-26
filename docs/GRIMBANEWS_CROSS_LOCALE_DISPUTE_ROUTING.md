# GrimbaNews — Cross-Locale Dispute Routing

**Status:** plan v0 (FR+EN only today; per-locale editor seats absent)
**Owner:** Lucy Leai (CEO) on policy + Liam Smith (PM) on routing rules + per-locale editor TBD (S1401 dependency)
**Walks:** Mythos S1429 (Cross-locale dispute routing) deferred → partial
**Gating dependency:** Multi-editor seats (S1401) + per-locale moderation policy (S1145) + ≥1 active editor per locale.

## Why this exists

S1429 handles the case where a dispute originates in locale A but the original article was published in locale B (translation chain). Today every dispute lands on solo-operator Vader regardless of locale. The routing rules need definition before locale-2 editors exist.

## Today's surrogate

- All disputes (FR / EN) flow to single intake (`/api/contact`).
- Operator manually re-replies in the reader's locale via Vader's bilingual capability.

## Routing rules (target)

```
dispute.locale_of_origin (reader's UI locale)
  → primary route: editor whose locales_written includes locale_of_origin
  → fallback 1:    editor of post.canonical_locale
  → fallback 2:    editor-in-chief (any locale)
  → fallback 3:    Ombudsman (S2022)
```

## Translation-chain handling

- If dispute is "translation is wrong" → route to translator role (S1411 author_type='translator') AND original author.
- If dispute is "original is wrong" → route to original author irrespective of dispute locale.

## Locale-pair priority matrix (initial)

| Reader locale | Primary editor | Fallback |
|---|---|---|
| fr | FR editor | EN editor + auto-translate response |
| en | EN editor | FR editor + auto-translate response |
| es / pt_BR / de | locale editor if seat filled, else EN editor | escalate to editor-in-chief |

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1429)
- Sister docs: `docs/GRIMBANEWS_DISPUTE_ESCALATION_WORKFLOW.md`, `docs/GRIMBANEWS_PER_LOCALE_MODERATION_POLICY.md`
- Existing infra: `app/Http/Middleware/GrimbaLocaleEnforce.php`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
