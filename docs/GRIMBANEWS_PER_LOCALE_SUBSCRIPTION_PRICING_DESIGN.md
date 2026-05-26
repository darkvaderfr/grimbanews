# GrimbaNews — Per-Locale Subscription Pricing Design

**Status:** design v0 (no paid tier today; gates on S1211 Stripe install)
**Owner:** Ray Dalio (CFO) on price-elasticity model + Warren Buffett (CFO Iboga) on unit economics + Lucy Leai (CEO) on go/no-go
**Walks:** Mythos S1149 (per-locale subscription pricing) deferred → partial
**Gating dependency:** Paid tier infra (S1211 Stripe install) ships first. Per-locale FX + tax compliance + counsel review for each jurisdiction.

## Why this exists

S1149 honest-deferred as "no paid tier (lands with S1211)." Paid tier is unbuilt. The **per-locale pricing matrix** is operator-side and decidable now so when paid tier ships, the price tiers are pre-calibrated.

## Pricing principles

1. **PPP-adjusted pricing** — Purchasing Power Parity baseline; €5/mo in France ≠ same accessibility as $5/mo in India.
2. **Anchor on local median news subscription** — Le Monde €7.99/mo, NYT $4/mo digital, El País €11/mo, FAZ €36/mo, Folha R$22/mo.
3. **Mission-aligned, not maximum-extraction** — GrimbaNews is editorial-trust-first; price should be affordable, not extractive.
4. **Local payment methods matter** — Brazil needs Boleto + Pix; India needs UPI; China-mainland (if served) needs WeChat Pay / Alipay; SEPA covers EU.

## Per-locale tier draft (when paid tier ships)

| Locale | Monthly | Annual | Notes |
|---|---|---|---|
| FR / EN-EU | €5.99 / mo | €54 / yr | Below Le Monde; above-zero-floor accessibility |
| EN-UK | £4.99 / mo | £45 / yr | Equivalent to FR-EU at FX |
| EN-US | $5.99 / mo | $54 / yr | Below NYT digital |
| ES | €4.99 / mo | €45 / yr | Slightly below FR — ES audience PPP adjusted |
| PT-BR | R$ 14.90 / mo | R$ 134 / yr | Below Folha + Boleto/Pix support |
| DE | €5.99 / mo | €54 / yr | Below FAZ / Welt |
| IT | €4.99 / mo | €45 / yr | Mirror of ES PPP |
| AR (UAE) | AED 22 / mo | AED 200 / yr | Above-zero accessibility |
| AR (regional avg) | varies per country | varies | PPP-adjusted matrix per Maghreb / Gulf / Levant |
| JA | ¥600 / mo | ¥5,400 / yr | Below Nikkei; above-zero floor |
| ZH-CN (if served outside mainland) | $4.99 / mo | $45 / yr | Diaspora audience; mainland blocked |
| KO | ₩6,000 / mo | ₩54,000 / yr | Below Chosun Ilbo digital |
| RU (diaspora) | $4.99 / mo | $45 / yr | Russia-resident serves separately |
| HE | ILS 22 / mo | ILS 200 / yr | Below Haaretz digital |
| HI | INR 199 / mo | INR 1,800 / yr | Strong PPP adjustment; UPI critical |
| SW (KE/TZ/UG avg) | KES 350 / mo | KES 3,100 / yr | M-Pesa support critical |

## Payment-method coverage requirements

- **Stripe baseline**: cards (Visa, MC, Amex), Apple Pay, Google Pay — covers most locales
- **SEPA Direct Debit**: EU locales (one-time setup, recurring billing)
- **Boleto + Pix**: BR — must support both before PT-BR paid tier ships
- **UPI / Razorpay / PayU**: IN — Stripe in India lacks UPI; counsel + payments-eng decision needed
- **M-Pesa**: KE/TZ/UG — via Flutterwave or DPO Group integration
- **WeChat Pay / Alipay**: ZH — only if mainland audience targeted; otherwise diaspora cards suffice
- **Mada (KSA) / KNET (KW)**: Gulf locale-specific; via Stripe MENA or local PSPs

## Tax / VAT compliance (per S1269)

- EU: VAT MOSS via Stripe Tax
- UK: separate VAT MOSS post-Brexit
- BR: ICMS / ISS per state (complex; counsel required)
- IN: GST registration if revenue threshold exceeded
- US: per-state sales tax (Stripe Tax handles)
- Each non-EU jurisdiction needs counsel pass before launch

## PPP table source

PPP adjustments draw from World Bank PPP data + Big Mac Index sanity check. Operator (Ray + Warren) reviews quarterly.

## Acceptance gates

1. Stripe pricing tables created per locale with active prices
2. Tax compliance verified per jurisdiction (Maya + counsel)
3. Local payment methods enabled per locale-specific requirements
4. /pricing page rendered per-locale with PPP-adjusted display
5. Currency conversion correct in user dashboard (charge in local currency, display in local symbol)

## Things deliberately NOT in this design

- **Per-locale dynamic pricing based on IP** — banned; use locale-driven pricing only (transparent + non-discriminatory)
- **Per-locale discount codes at launch** — operator-side promotional later
- **Family plan per locale** — per S1264 deferred row

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1149 row)
- Sister docs: `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md`, `docs/GRIMBANEWS_PER_LOCALE_AD_CONSENT_RULES.md`
- Existing infrastructure: none (paid tier not shipped); future integration via Stripe + Botble payment hooks
