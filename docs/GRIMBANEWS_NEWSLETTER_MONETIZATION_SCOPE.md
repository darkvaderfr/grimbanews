# GrimbaNews — Newsletter Monetization Scope (Paid Tier)

**Status:** scope v0 (no paid tier; per-search vault digest is the surrogate)
**Owner:** Ray Dalio (CFO) on unit economics + Lucy Leai (Strategy) on positioning + Steve Jobs (CPO) on UX
**Walks:** Mythos S1281 (paid newsletter tier), S1282 (newsletter sponsorship slot), S1290 (newsletter monetization playbook) deferred → partial
**Gating dependency:** Payment processor onboarding (Stripe — vendor #13 per `docs/GRIMBANEWS_VENDOR_REGISTER.md`, triggers PCI DSS scope per `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md`) + paid-tier sub-roster + general-audience newsletter shipped (today only per-saved-search digest ships).

## Why this exists

S1281, S1282, S1290 share the same root: GrimbaNews has **email-delivery infrastructure** today (`App\Mail\GrimbaVaultDigestMail`, `App\Mail\GrimbaSavedSearchDigestMail`, weekly cron at `routes/console.php:255`), but **no paid tier**, **no general newsletter to sponsor**, and **no payment processor wired**. The three rows are all "decide what we charge for, build it" — that decision is a scope doc, not a code sprint. This doc proposes the scope.

## Today's newsletter infrastructure

- **Vault digest** — weekly cron emits per-member "your saved articles" digest via `App\Mail\GrimbaVaultDigestMail` + `resources/views/emails/vault-digest.blade.php`. Locked by `VaultDigestMailTest`.
- **Saved-search digest** — weekly Monday 04:55 emits per-segment matches (one member → many searches → one digest per match-group) via `App\Mail\GrimbaSavedSearchDigestMail` + `App\Support\GrimbaSavedSearches::matchingPosts()`. Locked by `SavedSearchAlertsTest`.
- **Newsletter subscription** — `newsletter_subscriptions` table (per `NewsletterBiasSignalTest`); subscribe form ships in FR/EN per Wave CCCCCCCCCC.
- **Unsubscribe** — Botble member `unsubscribed_at` flag + per-mail footer unsubscribe link (S1289 partial).
- **Delivery vendor** — LeafRelay (Acelle, vendor #7 per vendor register; Iboga-owned VPS).

## Proposed paid-tier scope (S1281)

**Tier name:** "GrimbaNews+" (placeholder; Lucy Leai final).

**Price points (Ray's unit-economics review required):**

- **Free** — vault digest + saved-search digest (today).
- **Reader+ €4/mo or €36/year** — bias-bar deep-dives, ad-free reading, full vault digest with NobuAI weekly recap, early access to in-house editorial briefs (gates on S1311+).
- **Pro €12/mo or €108/year** — Reader+ benefits + per-cluster source-aggregation export (CSV per `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md`), academic-tier API access (gates on S1693).
- **Newsroom €49/mo per seat** — Pro + embed widgets without "Powered by NobuAI" cap (gates on S1651), per-source content stream (S1323).

**Payment:** Stripe (vendor #13). Triggers PCI DSS scope statement re-scoping per `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md`.

**Gating dependencies:**
1. Stripe account + Stripe Connect (if revenue-share to per-source partners ships per S1326).
2. Per-tier sub-roster table (`subscriptions` — Stripe-managed).
3. Per-tier feature-flag gating (`App\Support\GrimbaTier::isPaid($member)`).
4. PCI DSS scope statement update.

## Proposed newsletter sponsorship slot (S1282)

**Inventory** (only viable once general-audience newsletter ships beyond per-search digest):

| Position | Format | Cap | Why |
|---|---|---|---|
| Top-of-digest | Single sponsor + "presented by" line | 1/issue | Premium |
| Mid-digest | Native ad unit (text + small image) | 1/issue | High viewability |
| Footer | "Brought to you by" + sponsor logo grid | 1/issue | Low-friction inventory |

**Brand-safety:** sponsor reviewed by Lucy Leai before campaign launches; same editorial-brand-safety layer as `App\Support\GrimbaIngestGuardrails` filter for ad-side content.

**Sales channel:** /advertise form (already ships per `App\Http\Controllers\AdvertiserLeadController`) routes newsletter-sponsorship leads to `grimba_advertiser_leads_sales_mailbox`. Add `inquiry_type` = `newsletter-sponsorship` to the form.

**Cap rule:** Newsletter sponsorship revenue caps at **20%** of total newsletter revenue (rest is subscription). Hard cap enforced by Lucy to keep reader-trust ratio.

## Monetization playbook (S1290)

**Sequence:**

1. **Quarter 1 (post-launch):** Stripe onboarding + Reader+ tier + sub-only feature gating. Target: 200 paid subs.
2. **Quarter 2:** Pro tier + dataset CSV exports + per-cluster export gating. Target: 50 Pro subs.
3. **Quarter 3:** General-audience newsletter ships (today only per-search digest). Newsroom tier + embed-widget gating opens.
4. **Quarter 4:** Newsletter sponsorship slots open at 20% revenue cap.

**KPIs:**

| Metric | Target Q1 | Target Q4 |
|---|---|---|
| Paid subs | 200 | 1000 |
| ARPU | €4 (Reader+ only) | €6 (mix of tiers) |
| Churn | <8% monthly | <5% monthly |
| Newsletter ARPU (sponsorship side) | n/a | €1 per active sub |

**Reader-trust guard:** No paywall on news content. Paywall only on **deep-dives, datasets, embed-widgets, ad-free reading**. News stays free per editorial mission.

## Compliance carry-over

- **CAN-SPAM** — footer unsubscribe link + sender address + opt-out honored. Partial per S1289 today; full audit before paid-tier ships.
- **GDPR** — `docs/GRIMBANEWS_GDPR_ROPA.md` already enumerates newsletter as processing activity; refresh ROPA when Stripe joins as subprocessor.
- **PCI DSS** — `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md` re-scoping required at Stripe integration.

## Engineering effort estimate

- Stripe integration: 4 sprints.
- Tier gating + feature flags: 2 sprints.
- Paid-tier feature implementations: 8 sprints (deep-dives, ad-free, dataset gating).
- Sponsorship slot inventory + lead form update: 2 sprints.
- **Full ship: ~16-18 sprints once Stripe + scope sign-off lands.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1281, S1282, S1290)
- Vendor register: `docs/GRIMBANEWS_VENDOR_REGISTER.md` (vendor #13 Stripe pending)
- PCI scope: `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md`
- GDPR ROPA: `docs/GRIMBANEWS_GDPR_ROPA.md`
- Mail surfaces: `app/Mail/GrimbaVaultDigestMail.php`, `app/Mail/GrimbaSavedSearchDigestMail.php`, `resources/views/emails/*`
- Subscribe surface: `newsletter_subscriptions` migration + `NewsletterBiasSignalTest`
- Advertiser-lead pipeline: `app/Http/Controllers/AdvertiserLeadController.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
