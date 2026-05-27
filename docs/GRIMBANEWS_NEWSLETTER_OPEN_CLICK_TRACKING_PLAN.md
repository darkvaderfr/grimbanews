# GrimbaNews — Newsletter Open/Click Tracking Plan

**Status:** plan v0
**Owner:** Rajesh Kumar (Backend) + Jacob Lee (DevOps) + David Chen (Data)
**Walks:** Mythos S1286 (newsletter open / click tracking) deferred → partial
**Gating dependency:** tracking pixel endpoint + link rewriter + `email_events` table.

## Why this exists

Without open / click tracking the newsletter (vault digest, saved-search digest, daily report when it ships per S1382) is a black box. Editorial cannot iterate on subject lines, hero clusters, or send-time without these basic signals.

## v1 design

- Pixel: `<img src="/e/o/{event_token}.gif" width="1" height="1">` injected into HTML body.
- Link rewriter: every `<a href>` rewritten to `/e/c/{event_token}?u={base64url(original)}` 302-redirect.
- Tokens are signed (HMAC) and contain `(send_id, member_id_hash)` — never raw member id.

## Schema

```sql
CREATE TABLE email_events (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  send_id BIGINT NOT NULL,
  member_id_hash CHAR(64) NOT NULL,
  event_type ENUM('sent', 'delivered', 'opened', 'clicked', 'unsub', 'bounce') NOT NULL,
  ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  click_target_hash CHAR(64) NULL,
  ua_hash CHAR(64) NULL,
  ip_country VARCHAR(2) NULL,
  INDEX idx_send (send_id, event_type),
  INDEX idx_member (member_id_hash, ts)
);
```

## Privacy guardrails

- Honors DNT header (pixel returns 1x1 transparent gif but does NOT log event).
- Per-reader opt-out (stricter than email opt-in).
- IP truncation + country derivation only (no precise geo).
- 90-day raw retention; aggregate-only beyond that.

## Cross-references

Master plan: S1286. Sister: S1285 (unsub analytics), S1389 (daily report analytics), S1281+ (newsletter v2 set), S1499 (search A/B). Memory: `feedback_steve_design_language.md` (analytics is editorial tool, not surveillance).
