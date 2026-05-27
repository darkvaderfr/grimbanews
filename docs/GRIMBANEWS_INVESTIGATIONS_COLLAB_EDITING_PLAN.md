# GrimbaNews — Investigations Collaborative Editing Surface

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO)
**Walks:** Mythos S2206 (Long-form investigations collaborative editing) deferred → partial
**Gating dependency:** Multi-author investigation workflow.

## Why this exists

Investigations frequently involve multiple authors (lead investigator + co-investigator + editor + counsel reviewer). Collaborative editing surface needed with full revision history + per-clause comments.

## v1 platform pick

- **CryptPad (self-hosted):** end-to-end encrypted, per-document access tokens.
- Alternative: Outline Wiki (per-team) for less-sensitive collab.
- Both: per-document access logs for source-protection audit.

## Per-investigation editor workflow

1. Lead investigator creates per-investigation document.
2. Per-section commit + per-line comments.
3. Co-investigator + editor co-edit.
4. Counsel review window before publish.
5. Per-revision archive at investigation close.

## Source protection

- Per-document never references real source identities; use code-names.
- Per-investigation source-key kept offline (paper).
- Per-document access-token rotated quarterly.

## Cross-references

Master plan: S2206. Sister: `docs/GRIMBANEWS_INVESTIGATIONS_DATA_PIPELINE_PLAN.md`, `docs/GRIMBANEWS_SECUREDROP_TIP_INTAKE_PLAN.md`.
