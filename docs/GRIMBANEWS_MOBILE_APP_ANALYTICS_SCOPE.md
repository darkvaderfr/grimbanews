# GrimbaNews — Mobile App Analytics Scope

**Status:** plan v0 (no analytics SDK; no app shell)
**Owner:** David Chen (Data Scientist) defines events + Jacob Lee (DevOps) provisions SDK + Sara Chen (CISO) signs PII payload posture + Maya Patel (Compliance) on GDPR ROPA addition
**Walks:** Mythos S1157 (App analytics) deferred → partial
**Gating dependency:** Mobile shell shipped (S1152/S1153) + analytics vendor decision (Mixpanel vs Amplitude vs GA4 vs self-host) + GDPR consent banner mirror inside app

## Why this exists

S1157 unblocks per-feature usage measurement (which subsections readers visit, what they tap from notifications, retention curves). Today's web analytics signal (server-side Plausible per existing wiring) doesn't follow into a native shell.

## Today's surrogate

- **Server-side web analytics** — Plausible-style page-view counts via web-server logs (privacy-respecting).
- **Newsletter open + click** — none today (per S1286 deferred).
- **No event-based product analytics.**

## Vendor option matrix

| Vendor | Pros | Cons | Cost (10k MAU) |
|---|---|---|---|
| Mixpanel | strong cohort + funnel + retention | EU data-residency for paid plans | $25-$833/mo |
| Amplitude | best free tier (10M events/mo), strong UI | EU residency only on Scholarship plan | free → $61k+/yr |
| GA4 (Firebase) | free if used with FCM | Google data-residency concerns + breaks NobuAI provider-hiding rule (logo visible) | free |
| Self-host PostHog | full ownership, no third-party | requires ops bandwidth | self-host cost only |

**Recommendation:** Amplitude on free tier first 12 months, migrate to self-hosted PostHog when MAU crosses funded threshold.

## Event taxonomy (v0)

| Event | Properties | Purpose |
|---|---|---|
| `app_open` | platform, locale, source (notification/icon/share) | DAU |
| `screen_view` | screen_name, route | navigation funnel |
| `article_open` | post_id, cluster_id, source_position | content engagement |
| `dossier_open` | cluster_id, bias_distribution | MG / dossier metrics |
| `notification_tap` | category, push_id | push effectiveness |
| `vault_save` | post_id | coffre retention signal |
| `search_query` | query_length, results_count | search use |
| `share` | target_app, content_type | virality |
| `app_background` | session_duration_sec | session length |

## PII posture (Sara Chen / Maya Patel)

- **NO** member email / username in event payload.
- **YES** `member_id` (numeric FK) if logged in — pseudonymous in analytics tool.
- **NO** raw article text — only `post_id` / `cluster_id`.
- **NO** IP / device-fingerprint beyond what vendor SDK collects (configured to minimum).
- **YES** locale + platform — for cohort segmentation.
- Vendor entered in `docs/GRIMBANEWS_VENDOR_REGISTER.md` + `docs/GRIMBANEWS_GDPR_ROPA.md`.

## Consent gate

App-side consent banner mirrors `cookie_consent` cookie behavior on web — until reader accepts analytics, only `app_open` (anonymous) fires.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1157)
- Sister docs: `docs/GRIMBANEWS_MOBILE_CRASH_REPORTING_SCOPE.md`, `docs/GRIMBANEWS_MOBILE_APP_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
