# GrimbaNews — B2B API Outbound Webhook Delivery Plan

**Status:** plan v0
**Owner:** Rajesh Kumar (Backend) + Jacob Lee (DevOps) + Hannah Kim (Platform)
**Walks:** Mythos S1238 (B2B API v1 webhook delivery) deferred → partial
**Gating dependency:** Outbound webhook infra (queue + retry + signing key + delivery dashboard).

## Why this exists

Partners (B2B API consumers) currently poll `/feed.xml` or `/api/v2/*` (mostly deferred — S1241-S1245). Push delivery via webhooks reduces partner-side polling cost and enables real-time alert use cases (breaking-news, fact-check publish).

## v1 event taxonomy

| Event | Trigger |
|---|---|
| `cluster.created` | New cluster passes confidence threshold |
| `cluster.updated` | Cluster bias-mix or top-cluster-headline changes |
| `article.published` | Post status → published |
| `article.corrected` | Correction issued (S1591+) |
| `breaking.tagged` | Cluster auto-promoted to breaking |

## v1 contract

```json
{
  "event": "cluster.created",
  "occurred_at": "2026-05-27T14:23:11Z",
  "data": { "cluster_id": 12345, "headline": "...", "bias_mix": {...} },
  "delivery_id": "wh_abc123",
  "signature": "sha256=..."
}
```

- HTTP POST + `X-Grimba-Signature` HMAC-SHA256 over body using partner-side shared secret.
- 5s connect timeout, 10s read timeout.
- Retry policy: exponential backoff at 1m, 5m, 30m, 2h, 6h, 24h, then dead-letter.

## Schema

```sql
CREATE TABLE webhook_endpoints (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  partner_id BIGINT NOT NULL,
  url VARCHAR(512) NOT NULL,
  secret VARCHAR(128) NOT NULL,
  events JSON NOT NULL,
  active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE webhook_deliveries (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  endpoint_id BIGINT NOT NULL,
  event VARCHAR(64) NOT NULL,
  payload JSON NOT NULL,
  attempt INT DEFAULT 1,
  http_status INT NULL,
  delivered_at TIMESTAMP NULL,
  next_retry_at TIMESTAMP NULL,
  status ENUM('pending', 'delivered', 'failed', 'dead') DEFAULT 'pending'
);
```

## Anti-patterns

- No webhooks of PII fields.
- No partner can subscribe to high-volume events (e.g. `cluster.score_changed`) at v1 — keep volume sane.
- No fan-out to > 50 partner endpoints per second without throttle.

## Cross-references

Master plan: S1238. Sister: S1241-S1245 (article/source/cluster endpoints), S1239 (changelog), S1253 (usage dashboard).
