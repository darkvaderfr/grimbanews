# GrimbaNews — Transparency Report: Methodology Change Log Plan

**Status:** plan v0
**Owner:** Michael O'Connor (Tech Writer) + Henry Walker (Editorial) + Lucy Leai (Strategy)
**Walks:** Mythos S2010 (annual transparency report — methodology change log per-year) deferred → partial
**Gating dependency:** internal change log lives in git history; needs reader-facing per-year surface.

## Why this exists

A reader auditing GrimbaNews should be able to answer "what changed in the bias scoring between Jan and Dec 2026?" without diffing the repo. The methodology change log surfaces that history.

## v1 design

- New page: `/methodologie/historique`.
- Per-year subsection: changes to bias rubric, factuality scoring, ingest filters, classifier upgrades, source roster.
- Each change: date, scope, rationale, contributors, link to commit/PR if public OSS.
- ATOM feed at `/methodologie/historique.atom` for subscribers.

## Editorial workflow

- Each PR touching scoring/classifier logic requires a `methodology_change.md` snippet.
- Pre-merge check: snippet exists + has rationale ≥ 30 chars.
- Annual rollup compiled by Henry + Michael.

## Cross-references

Master plan: S2010. Sister: S2001 (transparency report), S2018 (year-over-year trend), `GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md`.
