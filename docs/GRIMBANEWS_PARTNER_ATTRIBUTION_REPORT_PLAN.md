# GrimbaNews — Partner Attribution Report Plan

**Status:** plan v0
**Owner:** David Chen (Data) + Michael O'Connor (Tech Writer) + Victor Garcia (BD)
**Walks:** Mythos S1444 (partner attribution report) deferred → partial
**Gating dependency:** partner content-share API (S1442) + per-partner attribution event ingest.

## Why this exists

Once partners republish Grimba content, both sides need a periodic report: how many articles were used, what topics, which clusters drove the most engagement on partner properties. Without this, partner relationships go cold and renewal conversations have no data foundation.

## v1 report shape

Monthly PDF + JSON sent to each partner contact, containing:

| Section | Content |
|---|---|
| Header | Partner name, period, # articles syndicated |
| Top clusters | 10 most-used clusters with topic breakdown |
| Topic mix | Pie chart: politics / climate / etc. |
| Bias mix | Distribution of source bias across syndicated content |
| Attribution health | % articles with back-link confirmed via Grimba spider |
| Outstanding issues | Misattributed articles, expired licenses, etc. |

## Data pipeline

- Partner-side back-link spider runs weekly; logs to `partner_attribution_events`.
- Monthly aggregation job writes `partner_monthly_reports` row + renders PDF.
- Partner can opt-in to a public attribution badge on their site (linked back to Grimba methodology page).

## Schema

```sql
CREATE TABLE partner_attribution_events (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  partner_id BIGINT NOT NULL,
  post_id BIGINT NOT NULL,
  partner_url VARCHAR(512) NOT NULL,
  detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  has_backlink BOOLEAN DEFAULT FALSE,
  attribution_score TINYINT DEFAULT 0,
  INDEX idx_partner_period (partner_id, detected_at)
);
```

## Cross-references

Master plan: S1444. Sister: S1442 (content-share API), S1445 (exclusivity), S1446 (takedown), S1447 (royalty), S1450 (launch retro).
