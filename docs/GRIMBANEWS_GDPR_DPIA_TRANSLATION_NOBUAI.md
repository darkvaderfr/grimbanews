# GrimbaNews — GDPR DPIA: Translation + NobuAI Summaries

**Status:** plan v0
**Owner:** Sara Chen (CISO) + counsel + Lisa Nguyen (data)
**Walks:** Mythos S1855 (GDPR DPIA — translation + NobuAI summaries) deferred → partial
**Gating dependency:** `app/Services/GrimbaTranslator.php` ships content (not reader PII) to providers; formal DPIA + provider-DPA register operator-side.

## DPIA scope

Per GDPR Art. 35: when transferring data to third-country providers (US-based LLM providers), DPIA may be required even for non-PII content if processing pattern likely affects rights.

## Current state (LOW DPIA risk for content)

- Content shipped to NobuAI providers: published article text (no reader PII).
- Per-translation: source article → provider → translated content.
- Per-NobuAI-summary: source article → provider → summary.
- No reader email, name, IP, or behavior shared with providers.

**Per content-only data: DPIA risk low.** But per-provider DPA still required (SCCs for US providers).

## Provider DPA register

Per Wave LLL `docs/GRIMBANEWS_VENDOR_REGISTER.md`:
- Anthropic Inc.: DPA + SCC executed.
- OpenAI: DPA + SCC executed.
- DeepL: DPA executed; EU-based, no SCC needed.
- LibreTranslate: open-source, no provider; self-hosted option.
- Mistral: EU-based (FR), DPA executed.

## Per-future-feature DPIA trigger

When per-reader personalized NobuAI summaries ship (gates on member-row personalization):
- DPIA mandatory: per-reader prompt may contain reading history.
- Per-DPIA: provider data residency review.
- Per-DPIA: per-reader opt-out provision.

## Cross-references

Master plan: S1855. Sister: `docs/GRIMBANEWS_GDPR_ROPA.md` (Wave LLL), `docs/GRIMBANEWS_GDPR_DPIA_HOMEPAGE_PERSONALIZATION.md`.
