# GrimbaNews — GDPR DSAR Full Bundle Plan

**Status:** plan v0 (no GDPR data subject access request pipeline; surrogate is operator-handled email request)
**Owner:** Sara Chen (CISO) on compliance posture + Rajesh Kumar (Backend) on bundle generator + Maya Patel (Compliance Officer) on audit trail
**Walks:** Mythos S1524 (Data export — GDPR DSAR full bundle) deferred → partial
**Gating dependency:** All member-data primitives (saved searches, follows, blocks, bookmarks, annotations, profile) shipped + counsel-defined SAR scope.

## Why this exists

S1524 fulfills the GDPR Article 15 (right of access) obligation programmatically. Today a SAR arrives by email and operator manually compiles. This breaks at scale and at deadline pressure (30 days response).

## Today's surrogate

- Operator pulls raw rows from MySQL by hand on email request.

## Bundle scope

A DSAR bundle includes (when each primitive ships):

| Data class | Source | Status |
|---|---|---|
| Profile | `members` row | live |
| Saved searches | `grimba_saved_searches` | live |
| Followed topics | `member_followed_categories` | S1513 deferred |
| Followed authors | `member_followed_journalists` | S1517 deferred |
| Followed clusters | `member_followed_clusters` | S1518 deferred |
| Blocked sources | `member_blocked_sources` | S1515 deferred |
| Bookmarks (coffre) | `member_bookmarks` | live |
| Annotations / highlights | `post_highlights` | S1541 deferred |
| Subscription history | `subscriptions` | S1261 deferred |
| Comment history | `comments` | S1361 deferred |
| Reading history (if opted-in) | `member_reading_events` | S1502 dependency |
| Contact / complaint history | `contact_messages` | live |
| Newsletter activity | `newsletter_events` | S1286 deferred |
| Web push subscriptions | `webpush_subscriptions` | S1302 deferred |
| Consent log | `consent_log` | live (per `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`) |

## Route + flow (target)

```
GET /account/data-export/request → confirm modal
POST /account/data-export/request → enqueue GenerateDsarBundleJob

  GenerateDsarBundleJob:
    - assemble JSON files per data class
    - zip with member_id + timestamp filename
    - upload to private S3 bucket with 7-day signed URL
    - email signed URL to member.email
    - log to dsar_requests (id, member_id, generated_at, bundle_hash)
```

## SLA + audit

- Generation must complete within 30 days (GDPR statutory).
- Target SLA: < 24h.
- All DSAR requests logged to `dsar_requests` for audit.
- Maya Patel reviews monthly.

## Counsel review needed

- What constitutes "personal data" under GDPR per the GrimbaNews schema?
- Are search query logs personal data when only `session_hash` is stored? (Likely yes if session_hash is linkable to member_id at any point.)
- Per-jurisdiction variant: California CCPA, Quebec Law 25, etc.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1524)
- Sister docs: `docs/GRIMBANEWS_SAVED_SEARCHES_EXPORT_PLAN.md`, `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
