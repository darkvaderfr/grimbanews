# GrimbaNews — Daily Report Analytics Plan

**Status:** plan v0
**Owner:** David Chen (Data Science) + Benjamin Lee (Data Eng) + Liam Smith (PM)
**Walks:** Mythos S1389 (daily report analytics) deferred → partial
**Gating dependency:** S1382 daily report shipped + S1286 newsletter open/click tracking primitive + `email_events` table.

## Why this exists

Without per-edition open / click / unsub metrics, the editorial team can't iterate on hero-cluster selection, subject lines, or send-time policy.

## v1 metrics

| Metric | Source |
|---|---|
| Sent count | `email_events.event_type = 'sent'` |
| Delivered | provider webhook |
| Opened (unique) | tracking pixel — privacy-respecting (no per-reader heatmap) |
| Clicked (unique) | rewritten links via `/r/{token}` |
| Click-by-section | sectioned tokens (hero / secondary / blindspot) |
| Unsub rate | unsub link |
| Pause rate | 7-day pause link |
| Inbox placement | optional inbox-placement provider integration |

## v1 dashboard

- `/admin/grimba/daily-report/analytics` — last 30 sends, per-edition row, sortable.
- Per-edition drill: send time, subject, hero cluster, open rate, top-3 clicked sections.
- Weekly digest of digest performance for editorial review.

## Privacy guardrails

- Aggregate-only dashboards (no per-reader open log surfaced).
- Tracking pixel respects DNT header.
- Per-reader opt-out for analytics tracking (stricter than opt-in for the email itself).

## Cross-references

Master plan: S1389. Sister: S1382 (daily report), S1286 (newsletter open/click), S1387/S1588/S1589 (A/B), S1390 (launch playbook).
