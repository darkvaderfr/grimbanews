# GrimbaNews — Native IAP Subscription Plan

**Status:** plan v0 (no IAP; gates on paid tier + Apple/Google merchant accounts)
**Owner:** Ray Dalio (CFO) on revenue model + Larry Ellison on subscription schema + Sara Chen on receipt-validation security + Nina Patel on plugin wiring
**Walks:** Mythos S1398 (Native subscription IAP) deferred → partial
**Gating dependency:** Paid tier (S1261 Stripe install) + Apple IAP entitlement (paid Apple Dev) + Google Play Billing + receipt-validation backend

## Why this exists

S1398 is the App Store-mandated payment path. Apple/Google take 15-30% but require digital-content subscriptions to use their IAP, no exceptions for news apps (a 2020 anti-trust carve-out only applies to "reader" apps reading content from outside the app — gray area for GrimbaNews; defer to counsel).

## Today's surrogate

- **No paid tier** — `account` page has billing placeholder; Stripe deferred to S1261.
- **Subscription scope** at `docs/GRIMBANEWS_MOBILE_APP_SUBSCRIPTION_SCOPE.md`.

## IAP product set

(per S1168 subscription scope)

| Apple Product ID | Google SKU | Tier | Cadence |
|---|---|---|---|
| `com.grimbanews.reader.monthly` | `grimba_reader_monthly` | Reader | monthly |
| `com.grimbanews.reader.annual` | `grimba_reader_annual` | Reader | annual |
| `com.grimbanews.pro.monthly` | `grimba_pro_monthly` | Pro | monthly |
| `com.grimbanews.pro.annual` | `grimba_pro_annual` | Pro | annual |

## Plugin

- `@capacitor-community/in-app-purchase` covers both stores with one API.

## Purchase flow

```
1. Reader taps "Subscribe" on /account/subscribe
2. App calls InAppPurchase.getProducts() — returns localized prices
3. Reader picks product → InAppPurchase.purchaseProduct(id)
4. Store handles payment UI
5. On success: receipt returned to app
6. App POSTs receipt to /api/iap/validate
7. Server validates with Apple/Google API
8. On valid: subscriptions row created, members.subscription_status = 'active'
9. Server returns subscription details
10. App refreshes UI to show "Reader/Pro active"
```

## Receipt validation (server side)

### Apple

```php
$response = Http::post('https://buy.itunes.apple.com/verifyReceipt', [
    'receipt-data' => $receiptB64,
    'password' => env('APP_STORE_SHARED_SECRET'),
    'exclude-old-transactions' => true,
]);
// status 0 = valid; status 21007 = sandbox, retry against sandbox URL
```

### Google

```php
$client = new Google_Service_AndroidPublisher($google_client);
$result = $client->purchases_subscriptions->get($packageName, $sku, $purchaseToken);
// returns paymentState, expiryTimeMillis, autoRenewing
```

## Reconciliation cron

`grimba:subscription-reconcile-iap` (daily):

- For each IAP-sourced subscription: re-check expiry.
- Apple: webhook S2S Notifications V2 (preferred) → real-time updates.
- Google: real-time developer notifications (RTDN via Pub/Sub) → real-time.
- Fallback: daily reconcile detects missed events.

## Cross-store identity

- Single member can subscribe on web (Stripe) OR app (IAP) — not both.
- App-side: if member already has Stripe sub, show "Manage via web" CTA, disable IAP.

## Tax + accounting (Ray Dalio)

- Stores collect VAT in supported markets.
- Operator receives net-of-commission after 30-45 day delay.
- Apple statement + Google Play Earnings report monthly.
- Recurring revenue reconciliation in `subscriptions` table flagged by `source ENUM('stripe','apple','google')`.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1398)
- Sister docs: `docs/GRIMBANEWS_MOBILE_APP_SUBSCRIPTION_SCOPE.md`, `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`, `docs/GRIMBANEWS_PER_LOCALE_SUBSCRIPTION_PRICING_DESIGN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
