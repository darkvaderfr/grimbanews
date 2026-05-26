# GrimbaNews — Mobile App Subscription Scope

**Status:** plan v0 (no paid tier yet; gates on S1261 Stripe install)
**Owner:** Ray Dalio (CFO) on pricing + Warren Buffett on margin model + Larry Ellison on subscription schema + Nina Patel on IAP wiring + Sara Chen on receipt-validation posture
**Walks:** Mythos S1168 (App subscription) deferred → partial
**Gating dependency:** Paid tier infra (S1261 Stripe install) + Apple IAP entitlement + Google Play Billing setup + receipt-validation backend

## Why this exists

S1168 covers the in-app purchase path. App stores require IAP for digital subscriptions (Apple 30% / 15% second year, Google 15%). Web Stripe gateway is the parallel path for readers signing up outside the app.

## Today's surrogate

- **Newsletter monetization scope** at `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md` (tiers planned, billing not wired).
- **Per-locale subscription pricing design** at `docs/GRIMBANEWS_PER_LOCALE_SUBSCRIPTION_PRICING_DESIGN.md`.
- **No Stripe** + **no IAP** today.

## IAP plugin pick

- `@capacitor-community/in-app-purchase` — covers iOS StoreKit + Android Play Billing.
- Alternative: native bridge written ourselves (heavier maintenance; not picked).

## Subscription products

| Product ID | Tier | Cadence | Price (USD, EUR baseline) |
|---|---|---|---|
| `grimba_reader_monthly` | Reader | monthly | $5 / €5 |
| `grimba_reader_annual` | Reader | annual | $45 / €45 |
| `grimba_pro_monthly` | Pro | monthly | $12 / €12 |
| `grimba_pro_annual` | Pro | annual | $99 / €99 |

(Per-locale variants per `docs/GRIMBANEWS_PER_LOCALE_SUBSCRIPTION_PRICING_DESIGN.md`.)

## Server-side receipt validation

- iOS: validate receipt against Apple App Store servers (`buy.itunes.apple.com/verifyReceipt`).
- Google: validate purchase token against Play Developer API.
- Both: write to `subscriptions` table (gates on S1261 schema).
- On valid: `members.subscription_status = 'active'`, `subscription_expires_at = ...`.
- Cron `grimba:subscription-reconcile-iap` daily — handles auto-renew + grace period.

## Tax + VAT

- App store handles tax collection in supported markets.
- Operator side: account for store-handled vs Stripe-handled split in monthly reconciliation (Ray Dalio + accounting).

## Cross-store identity

- IAP receipt → app sends receipt to server → server links to `member_id`.
- Member email used as bridge across web Stripe + iOS IAP + Android IAP.
- Single subscription per member — duplicates blocked at backend.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1168)
- Sister docs: `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`, `docs/GRIMBANEWS_PER_LOCALE_SUBSCRIPTION_PRICING_DESIGN.md`, `docs/GRIMBANEWS_NATIVE_IAP_SUBSCRIPTION_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
