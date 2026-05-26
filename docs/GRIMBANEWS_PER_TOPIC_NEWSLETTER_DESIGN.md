# GrimbaNews — Per-Topic Newsletter Design

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM) + per-topic editor
**Walks:** Mythos S1036 (per-topic newsletter) deferred → partial
**Gating dependency:** Newsletter v2 (S1271-S1290 partial per Wave LLL) + v2 taxonomy (S1031 partial per Wave UUUU) + per-topic editor.

## Concept

Reader subscribes to a topic (Politics, Sciences, Climat, etc.). Receives a topic-scoped weekly digest of:
- Top-3 dossiers (most multi-source coverage)
- Top-2 Middle Ground stories (where left and right converged)
- Top-2 Blindspots (one-camp coverage worth knowing)
- Editor's pick: 1 standout story + commentary

## Schema

`members.topic_subscriptions` JSON column or `member_topic_subscriptions` table:
```
member_id | topic_id | created_at | last_sent_at | unsubscribed_at
```

## Cadence

- Weekly send (Saturday morning per locale).
- Per-locale delivery time configurable.
- Editor-curated copy at the top of each digest.
- Auto-generated body (top-N from cluster query).

## UX touchpoints

- `/account/newsletter` settings: per-topic toggle.
- Onboarding flow offers topic subscriptions after first 3 visits.
- Email footer carries unsubscribe + topic-management deep link.

## Per-topic editor role

For each topic with active newsletter:
- Edits the curator's-pick copy weekly.
- Reviews bot-generated digest for accuracy.
- 30-min commitment per week per topic.

## Cost

- Weekly send ~4× / month / reader / topic.
- If reader subs 3 topics = 12 emails/month.
- SES cost ~$0.10 / 1000 emails — negligible.
- Editor time: 30 min/wk × 40 topics × 4 wk = 80 hours/month (gates on per-topic editor hires).

## Cross-references

Master plan: S1036. Sister: per-region daily digest (`docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md` Wave KKKK), newsletter monetization scope (Wave LLL).
