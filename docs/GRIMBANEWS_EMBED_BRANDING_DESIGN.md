# GrimbaNews — Embed Branding ("Powered by GrimbaNews")

**Status:** plan v0
**Owner:** Alex Morgan (UI/UX) + Lucy Leai (Strategy)
**Walks:** Mythos S1658 (embed widget branding) deferred → partial
**Gating dependency:** Wave SUB-21 embed.js bundle live; brand-purity locked by `GrimbaNobuAiBrandPurityTest` (user-facing copy = NobuAI only, never external provider names).

## Why this exists

Embedded widget needs visible GrimbaNews attribution + driving back to grimbanews.com. Brand-purity rule: never expose external LLM provider names on reader surfaces.

## v1 branding

- Per-embed footer line: "Comparaison alimentée par grimbanews.com" / "Powered by GrimbaNews".
- Per-embed logo: small (16px) GrimbaNews wordmark.
- Per-embed click-through to /comparatif/{id}.
- Per-embed methodology cross-link (small, optional).

## NobuAI branding rule (per `feedback_nobuai_model_branding.md`)

When embed includes NobuAI-generated content (summary, insight):
- Label: "Résumé NobuAI" / "NobuAI insight"
- NEVER expose: Anthropic, OpenAI, Claude, GPT, etc.
- Per-embed locked by `GrimbaNobuAiBrandPurityTest` regression.

## Per-publisher whitelabel (Pro+ tier)

- Pro tier: optional "Embedded by {publisher-name}" caption.
- Enterprise: full whitelabel (remove GrimbaNews wordmark, keep methodology cross-link).

## Cross-references

Master plan: S1658. Sister: `docs/GRIMBANEWS_EMBED_JS_SNIPPET_GENERATOR.md`, `feedback_nobuai_model_branding.md`, `tests/Feature/GrimbaNobuAiBrandPurityTest.php`.
