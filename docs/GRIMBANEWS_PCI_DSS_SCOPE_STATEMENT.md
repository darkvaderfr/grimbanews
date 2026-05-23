# GrimbaNews — PCI DSS Scope Statement (Not Applicable)

**Status:** formal scope statement (PCI DSS = Not Applicable today)
**Owner:** Sara Chen (CISO) + Ray Dalio (CFO) co-signed for posture
**Walks:** Mythos S1841 (PCI DSS scope determination) deferred → partial
**Gating dependency:** None — this scope statement is the formal record that no cardholder data is processed. Re-scoping triggers are itemized below.

## Why this exists

S1841 was honest-deferred with the note "no formal scope-statement document; cardholder-data environment is empty (no payment processor integration). Surrogate: grep for stripe|paypal|braintree|adyen|payment returns zero matches outside `GrimbaPlaceholderController.php`." That deferral was honest but the formal scope statement *itself* is the missing piece — auditors / customers asking about PCI DSS posture want a signed statement saying "we don't process CHD and here's why."

## Statement

**GrimbaNews does not store, process, or transmit cardholder data (CHD) or sensitive authentication data (SAD).**

The PCI DSS standard (v4.0 as of 2024) applies only to entities that store, process, or transmit CHD. Per PCI DSS § "Scope of PCI DSS Requirements," the cardholder data environment (CDE) is empty for GrimbaNews; therefore **the entire PCI DSS standard is Not Applicable (N/A)** today.

## Evidence

Codebase grep (2026-05-22):

```bash
$ grep -rE "stripe|paypal|braintree|adyen|cardholder|pan_number|card_number" \
    app/ config/ routes/ database/ resources/ platform/ public/ \
    --include="*.php" --include="*.blade.php" --include="*.js" \
    | grep -v "vendor/" | grep -v "node_modules/"
```

Returns: zero matches outside `app/Http/Controllers/GrimbaPlaceholderController.php` (which is the bias-color stripe SVG generator — the word "stripe" in a color/border context, not the payment processor).

Database schema grep (2026-05-22):

```bash
$ grep -rE "payment|card|pan|cvv|cardholder" database/migrations/
```

Returns: zero matches.

## What we have instead

- **Subscriber model** — email-only opt-in to weekly digest. Tracked in `subscribers` table. No payment.
- **Member model** — Botble member auth (email + password hash). No payment.
- **Advertiser leads** — `App\Http\Controllers\AdvertiserLeadController` — captures lead intake form, routes to sales mailbox. No payment.
- **Vault digest** — server-emits weekly digest via `app/Mail/GrimbaVaultDigestMail.php`. No payment.

All revenue motions today are deferred per S1211 monetization band. There is no checkout flow, no payment method on file, no subscription billing.

## Re-scoping triggers

PCI DSS scope **MUST** be re-evaluated and this statement re-issued if **any** of the following lands:

1. **Stripe / PayPal / Braintree / Adyen integration** (paid tier per S1211 monetization). At that point a Self-Assessment Questionnaire (SAQ) — likely SAQ-A or SAQ-A-EP depending on integration pattern — becomes required.
2. **Stored-credential subscription billing** (recurring payment with our backend retaining a tokenized card reference). Same as #1 + additional controls.
3. **Donation flow** with credit-card capture (even via embedded iframe).
4. **B2B invoicing** that includes corporate-card payment (per S1991 enterprise tier band).
5. **Institutional license payment** via credit card per S1981 (PO/Net-30 invoicing without CC would not trigger PCI DSS).

For each trigger:

- Sara Chen + Ray Dalio meet within 7 days of integration kickoff.
- Determine SAQ tier (A / A-EP / B / B-IP / C / C-VT / D).
- Engage QSA if Level 1 merchant threshold projected.
- Update this statement.
- Add controls per applicable SAQ.
- Re-issue scope statement with new date.

## Integration-pattern preference (when monetization S1211 lands)

To minimize PCI DSS scope footprint, the integration pattern preference order is:

1. **Stripe Checkout (hosted)** — embedded iframe; we never touch CHD; SAQ-A applies.
2. **Stripe Elements (tokenized)** — JS-tokenized in browser; we never touch raw CHD; SAQ-A-EP applies.
3. **Stripe Server-side API with our backend handling CHD** — full SAQ-D; avoid unless forced.

Stripe Checkout (option 1) is the strong preferred path — keeps us in SAQ-A scope.

## Signoff

- **Sara Chen (CISO):** {to be signed at first issuance — currently v0 draft}
- **Ray Dalio (CFO):** {to be signed at first issuance — currently v0 draft}
- **Vader (operator):** {to be signed at first issuance — currently v0 draft}

Note: signatures are physical / digital signature collection — not in scope of this auto-generated doc; this is the draft text awaiting signature.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1841 row; gates for S1842-S1850)
- ISMS scope: `docs/GRIMBANEWS_ISMS_SCOPE.md`
- Vendor register (Stripe = vendor #13 planned): `docs/GRIMBANEWS_VENDOR_REGISTER.md`
- Monetization gate: master plan S1211 (paid tier)
- Standards reference: PCI DSS v4.0 (Payment Card Industry Data Security Standard, Council 2024)
