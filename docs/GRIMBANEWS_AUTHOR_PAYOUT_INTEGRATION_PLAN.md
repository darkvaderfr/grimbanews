# GrimbaNews — Author Payout Integration Plan

**Status:** plan v0 (no payout infra; no Stripe / Wise integration)
**Owner:** Warren Buffett / Ray Dalio (CFO) on unit economics + Rajesh Kumar (Backend) on integration + Sara Chen (CISO) on PII / KYC
**Walks:** Mythos S1419 (Author payout integration) deferred → partial
**Gating dependency:** Contributor program (S1451+) live + paid-tier infra (S1261 Stripe install) live + journalist table (S1411) with payout fields.

## Why this exists

S1419 connects per-author analytics (S1418) to actual money. Today no payouts run. The integration choice (Stripe Connect vs Wise vs PayPal) is gated on contributor program scope (revenue share vs flat-fee vs per-piece).

## Today's surrogate

- No payouts. Operator pays selectively via direct Stripe payment links (manual).

## Provider comparison

| Provider | Pros | Cons | Best fit |
|---|---|---|---|
| Stripe Connect Express | Mature, KYC built-in, global, integrates with subscription billing | 2% + $2/payout fee; payout-only flow not as smooth as Connect Standard | Default if S1261 Stripe in place |
| Wise (TransferWise) Business | Cheap FX, multi-currency holding | Less mature API, manual reconciliation | Cross-border, large-USD-to-small-EUR splits |
| PayPal Mass Payments | Wide reach | Fee structure unfavorable, weak audit trail | Fallback only |

## Recommended path (Ray decision pending)

1. **Wait for S1261 Stripe install.**
2. **Provision Connect Express accounts per active contributor** (KYC via Stripe, not GrimbaNews).
3. **Monthly payout cycle** triggered by `grimba:payout-cycle` Artisan command (deferred).
4. **Payout calculation source:** S1418 author analytics × revenue-share formula (S1453 rate card dependency).

## Schema additions (target)

```sql
ALTER TABLE journalists ADD COLUMN stripe_connect_id VARCHAR(64) NULL;
ALTER TABLE journalists ADD COLUMN payout_method ENUM('stripe','wise','none') DEFAULT 'none';
CREATE TABLE journalist_payouts (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  journalist_id BIGINT NOT NULL,
  cycle_start DATE, cycle_end DATE,
  amount_cents INT, currency CHAR(3),
  provider VARCHAR(16),
  provider_payout_id VARCHAR(64),
  status ENUM('pending','sent','failed','disputed'),
  created_at TIMESTAMP, updated_at TIMESTAMP,
  INDEX (journalist_id, cycle_end)
);
```

## Tax / KYC posture (Sara Chen)

- KYC delegated to Stripe Connect (out of scope for GrimbaNews).
- 1099 / equivalent reporting handled by Stripe.
- GrimbaNews retains `journalist_payouts` ledger for auditing.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1419)
- Sister docs: `docs/GRIMBANEWS_AUTHOR_ANALYTICS_DASHBOARD_SCOPE.md`, `docs/GRIMBANEWS_AUTHOR_TABLE_SCHEMA.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
