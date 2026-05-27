# GrimbaNews — Science v2 Per-Discipline Buckets

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Lucy Leai (Strategy) + science editor TBD
**Walks:** Mythos S2190 (Science v2 per-discipline buckets) deferred → partial
**Gating dependency:** v2 taxonomy (Wave UUUU) live; current Science bucket is flat.

## Why this exists

Current `editorial_category='sciences'` is flat. Readers interested in physics don't want all biology too. Per-discipline buckets enable per-discipline subscriptions + per-discipline editor focus.

## Proposed sub-buckets

Per Wave UUUU v2 taxonomy proposal, Science block:

- **28. Sciences fondamentales** — physics, astronomy, basic biology, chemistry, math.
- **28a. Physique** — fundamental + applied + materials.
- **28b. Astronomie** — observation + cosmology + planetary.
- **28c. Biologie** — molecular + evolutionary + ecology.
- **28d. Chimie** — synthesis + analysis + materials.
- **28e. Mathématiques** — pure + applied + statistics.
- **29. IA** — research + methods + ethics + regulation.
- **30. Cybersécurité** — vulnerabilities + defense + policy.
- **31. Plateformes** — social-media research + content-moderation studies.
- **32. Bigtech** — corporate research + antitrust angle.

## Migration path

1. Per-cluster auto-classifier (NLP-based) tags into sub-buckets.
2. Editor reviews ~5% sample for accuracy.
3. UI: /sujets/sciences expands to sub-bucket tabs.
4. Per-sub-bucket newsletter subscription.

## Cross-references

Master plan: S2190. Sister: `docs/GRIMBANEWS_TOPIC_TAXONOMY_V2_DESIGN.md`, `docs/GRIMBANEWS_PEER_REVIEWED_JOURNAL_COVERAGE_PLAN.md`.
