# GrimbaNews Africa / International Editorial Pivot

**Status:** active product direction  
**Date:** 2026-04-30  
**Scope:** reader editions, article presentation, category routes, NobuAI editorial profile, image retrieval, and ad placement

## Product Direction

GrimbaNews now treats the public editorial surface as two editions:

- **Afrique**: continent-first coverage, including African institutions, economies, societies, sovereignty questions, regional power, diaspora impact, and under-covered stories.
- **International**: global coverage read through consequences for African publics, intellectuals, institutions, businesses, and diasporas.

The old France / UK / US / Canada / Europe / Monde style region taxonomy is legacy. It may remain in historical content, but new public navigation, edition filtering, and category classification should use only Afrique and International.

## Shipped First Pass

- Public edition UI is a two-choice toggle: Afrique / International.
- `/region/set` accepts only `africa` and `international`, while folding legacy values into International.
- Reader category chips and onboarding topic choices show only Afrique and International.
- Category seeding creates/demotes toward the two canonical categories.
- The classifier assigns new posts to Afrique when source or content is Africa-specific; otherwise International.
- `/afrique` and `/international` routes set the edition cookie and return readers to the edition-filtered home surface.
- Story and orphan article heroes now always render an image, falling back to the Grimba placeholder route when extraction has no usable image.
- Story pages now expose dedicated ad locations: `grimba_story_after_hero`, `grimba_story_mid`, and `grimba_story_sidebar`.
- Article image enrichment records provenance when the migration is applied: source URL, extraction method, extraction time, and last failure reason.
- The ads location registry exposes the new story slots so they are configurable from the existing ads backend.

## NobuAI Editor-In-Chief

NobuAI is now configurable from `/admin/grimba/translation` through these editable profile fields:

- Mission
- Editorial soul
- Capabilities
- Pan-African references
- Guardrails

The default profile uses a Pan-African analytical lens associated with traditions around Kwame Nkrumah, Patrice Lumumba, Nelson Mandela, Nathalie Yamb, and similar sovereignty-focused thinkers. NobuAI must not imitate a living person, invent facts, endorse parties/candidates, or turn analysis into political instructions.

Cluster summaries can include a `Perspective africaine` insight line alongside the existing source-framing lines.

## Next Database Pass

Do not force these into the current schema without a migration review:

- Article image provenance phase 2: track local media path, confidence, dimensions, license/caption text, and per-image audit history.
- Article content blocks: store normalized block order so inline ads can be inserted between paragraphs without brittle HTML splitting.
- Ad placements phase 2: define consent requirements, subscriber suppression, frequency caps, and campaign metadata beyond the ads plugin's location registry.
- Edition tagging: add a durable edition field or pivot if cookie/source-country filtering proves too coarse for editorial decisions.

## Acceptance Bar

- New public category work must route through Afrique / International.
- New NobuAI work must respect the admin-editable profile and keep provider names hidden from readers.
- New article layouts must reserve image and ad space to avoid CLS.
- Africa mode must never hide African-source coverage because of a stale legacy region value.
