# GrimbaNews — Per-Reader Weekly Engagement Digest

**Status:** plan v0
**Owner:** Liam Smith (PM) + Steve Jobs (CPO) + Lucy Leai (Strategy)
**Walks:** Mythos S1660 (per-reader weekly digest) deferred → partial
**Gating dependency:** Per-reader reading-time analytics (Wave AAHH) + newsletter v2.

## Why this exists

Weekly per-reader recap: what they read, what's new in their followed topics, what they missed. Drives retention through Sunday-evening engagement spike.

## v1 design

Sunday 17:00 local-time per-reader cron:

```
Subject: GrimbaNews — votre semaine
Body:
  - Articles lus cette semaine: {n}
  - Temps de lecture: {m} min
  - Top 3 sujets lus
  - Vous avez manqué: 5 dossiers majeurs dans vos sujets suivis
  - Recommandé pour la semaine prochaine: 3 dossiers
```

Per-reader opt-in via `/account/newsletter`.

## Per-reader personalization

- Reading history → top 3 topics inferred.
- Followed-topics list → curated picks.
- Per-reader yearly wrap (Wave AAKK) leverages weekly history.

## Cost

- 1 personalized email per opted-in reader per week.
- SES ~$0.10 / 1000 emails — negligible.

## Cross-references

Master plan: S1660. Sister: `docs/GRIMBANEWS_PER_USER_READING_TIME_ANALYTICS_PLAN.md`, `docs/GRIMBANEWS_PER_TOPIC_NEWSLETTER_DESIGN.md`.
