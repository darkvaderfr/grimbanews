# GrimbaNews — Newsletter Per-Mail Unsubscribe Analytics Plan

**Sprint ID:** S1285
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Newsletter unsubscribe analytics`
**Walk wave:** BBBB

## Gating dependency

Per-mail unsubscribe analytics needs:

- An email-event tracking SDK (open / click / unsub webhooks from the mail provider)
- Per-mail `mail_id` propagation through every send → click → unsub path
- An `email_events` table with cardinality safe for high-volume newsletters
- A `/admin/grimba/newsletter/{id}/events` surface
- DPIA update for per-mail tracking under GDPR (S1855, deferred)

None of these ship today.

## Surrogate-now infra

- **Botble `members.unsubscribed_at` flag** — global unsub state (yes/no), not per-mail
- **Bounce-rate via mail-server logs** — Postmark / SES / SMTP logs give bounce + delivery; manual export only
- **Per-newsletter open-tracking pixel** — could be wired today via a `1x1` image route but not currently rendered

## Honest framing

Per-mail unsub analytics requires a real lifecycle ESP webhook contract. Lands with newsletter v2 (S1281+ band). The global unsub flag is sufficient for compliance today; per-mail attribution is a growth-optimization signal, not a compliance gap.

## Owners

- **Product:** Liam Smith — analytics scope + attribution rules
- **Backend:** Rajesh Kumar — webhook receivers + event ledger
- **Marketing:** Henry Walker / Olivia Davis — newsletter cadence optimization
- **Compliance:** Maya Patel — DPIA delta + consent banner update
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1285 row)
- Newsletter v2 band anchor: `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
