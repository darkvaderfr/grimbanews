# GrimbaNews — Paid Tier (Stripe Install) Surrogate Plan

**Sprint ID:** S1261
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Paid tier infra (Stripe install)`
**Walk wave:** CCCC

## Gating dependency

Installing Stripe needs:

- `composer require stripe/stripe-php`
- `stripe_secret_key` + `stripe_publishable_key` + `stripe_webhook_secret` settings
- `subscriptions` + `subscription_items` + `invoices` migrations
- Webhook endpoint with signature verification
- Customer Portal config (Stripe-hosted self-service)
- VPS firewall allow-list for Stripe webhook IPs

None of these ship today.

## Surrogate-now infra

- **`config/grimba_credits.php`** — internal cost-config pattern that the Stripe key-config would mirror
- **`AdvertiserLeadController`** — POST-with-webhook idempotency pattern we'd reuse for Stripe webhooks
- **`/account` page** — placeholder UI ready to host the Customer Portal redirect button
- **Reader email collection** — already in place via newsletter_subscriptions; that email is the Stripe customer identifier

## Honest framing

This is *the* gating sprint for the entire paid-tier cluster (S1261-S1270 + S1326 + S1388 + S2107). Cannot ship API billing (S1254), subscription analytics (S1270), per-partner revenue share (S1326), or subscriber tiering (S882) without it.

## Owners

- **Finance:** Warren Buffett — Stripe account setup + payout config
- **Strategy:** Ray Dalio — pricing model approval
- **Backend:** Rajesh Kumar — package install + webhook
- **Security:** Maya Patel — webhook secret + idempotency
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1261 row)
- Billing meter scope: `docs/GRIMBANEWS_API_BILLING_METER_SCOPE.md`
- Subscriber ad-free flag: S882 (gated on this row)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
