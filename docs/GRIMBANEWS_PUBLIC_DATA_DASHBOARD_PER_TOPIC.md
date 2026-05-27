# GrimbaNews — Public Data Dashboard Per Topic

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Lisa Nguyen (data) + per-topic editor
**Walks:** Mythos S1668 (public data dashboard per topic) deferred → partial
**Gating dependency:** v2 taxonomy (Wave UUUU) + per-region public-data viz embed pipeline (Wave YYYY).

## Why this exists

Beyond "see the news," readers benefit from "see the structural data behind the news." Per-topic dashboard surfaces curated public data (election polls, climate stats, economic indicators).

## v1 design

`/sujets/{topic-slug}/donnees` page renders:
- 3-5 curated public-data viz embeds (per Wave YYYY)
- Cross-link to recent dossiers on the topic
- Per-source attribution + license note
- Update cadence per data source

## Per-topic dashboard inventory

- /sujets/politique/donnees: election polls, parliamentary votes, party membership
- /sujets/economie/donnees: GDP, inflation, unemployment, currency rates
- /sujets/climat/donnees: temperature anomaly, CO2, COP commitments
- /sujets/santé/donnees: vaccination rates, healthcare access, public-health alerts
- Other topics: per-editor curation

## Cross-references

Master plan: S1668. Sister: `docs/GRIMBANEWS_PER_REGION_PUBLIC_DATA_VIZ_EMBED_PLAN.md`, `docs/GRIMBANEWS_TOPIC_TAXONOMY_V2_DESIGN.md`.
