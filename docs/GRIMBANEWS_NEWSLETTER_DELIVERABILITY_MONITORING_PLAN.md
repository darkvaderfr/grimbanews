# GrimbaNews — Newsletter Deliverability Monitoring Plan

**Status:** plan v0
**Owner:** Jacob Lee (DevOps) + Lisa Nguyen (data)
**Walks:** Mythos S1702 (newsletter deliverability monitoring) deferred → partial
**Gating dependency:** Sentry / Postmark inbox-monitoring integration.

## Why this exists

Newsletters drive 30%+ of editorial-product engagement. If bounce rate spikes or domain reputation tanks, traffic dries up silently.

## v1 monitoring

Per-send batch:
- **Sent count vs queued count**
- **Soft-bounce rate** (target < 1%)
- **Hard-bounce rate** (target < 0.3%; spike triggers ops alert)
- **Spam-folder rate** (sample via Postmark inbox-placement test)
- **Open rate** (target > 30%)
- **Click rate** (target > 4%)
- **Unsubscribe rate** (target < 0.5%)

## Per-domain reputation

- SES sender reputation: monitor via AWS console
- Domain DKIM + SPF + DMARC: monthly check
- Per-recipient-domain bounce rate (gmail.com, outlook.com, etc.)

## Alerting

- Spike alerts via Wave FFFFFFFFFFF slack_webhook pattern
- Bounce-rate > 1% → P1 alert
- Open-rate < 20% → P2 alert (content quality investigation)

## Cross-references

Master plan: S1702. Sister: `docs/GRIMBANEWS_PAGING_MATRIX.md`, `docs/GRIMBANEWS_DAILY_REPORT_EMAIL_TEMPLATE_DESIGN.md`.
