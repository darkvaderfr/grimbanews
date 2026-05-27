# GrimbaNews — Per-Source Onboarding Email Automation

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM)
**Walks:** Mythos S1663 (per-source onboarding email automation) deferred → partial
**Gating dependency:** Transactional email pipeline + per-source contact field.

## Why this exists

When a new source is added via partnership program (Wave LLL), the source's contact wants confirmation + monitoring access. Manual outreach is operator-time. Automation closes the loop.

## v1 workflow

When `news_sources.partner_slug IS NOT NULL AND created_at > 7d ago`:

1. Day 0: welcome email to source contact (operator-set).
   - Source brief: "your articles are now in our aggregation"
   - Per-partner deep-link to spotlight page (Wave AAFF)
   - Link to dashboard preview
2. Day 7: status email — first 7 days of ingest stats.
3. Day 30: monthly stats email + invitation to per-quarter review call.

## Per-partner dashboard

`/partenaire/{slug}/dashboard` (auth via per-partner token):
- Articles ingested this month
- Top-3 most-read partner articles
- Per-cluster contribution count
- Reader feedback on partner content

## Cross-references

Master plan: S1663. Sister: Wave LLL partnership program docs.
