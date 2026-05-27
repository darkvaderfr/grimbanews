# GrimbaNews — Hate Speech Filter Design

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Lucy Leai (Strategy) + Lisa Nguyen (data)
**Walks:** Mythos S1711 (hate-speech filter) deferred → partial
**Gating dependency:** NobuAI judge + per-locale linguistics review.

## v1 — keyword + NobuAI hybrid

1. Per-locale keyword list (curated, ~200 terms).
2. NobuAI judge for ambiguous cases ("yes" / "no" classification).
3. Per-flagged text routed to editor mod queue (Wave KKKK).

## Surfaces filtered

- Article body (rare; publisher self-filters).
- Comments (more common; real-time).
- Reader fact submissions (Wave AAKK).
- Reader feedback widget (Wave AAMM).
- Q&A submissions (Wave AABB).

## Per-locale tuning

- FR, EN, DE, ES, PT-BR each have own keyword list curated by per-locale editor.
- DE: §130 StGB Volksverhetzung is strictest; default-conservative.
- FR: nuance for legitimate critique vs hate.

## Cross-references

Master plan: S1711. Sister: `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md` (Wave LLL), comment moderation pack from KKKK.
