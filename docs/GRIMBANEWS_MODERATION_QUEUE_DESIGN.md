# GrimbaNews — Moderation Queue Design

**Status:** design v0 (no UGC today; `/admin/grimba/rss-drafts` is the editorial-side surrogate)
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO) on safety controls + Steve Jobs (CPO) on UX
**Walks:** Mythos S1351 (moderation queue scope), S1591 (moderation queue schema) deferred → partial
**Gating dependency:** UGC primitive ships (comments per S1361-S1370 deferred / reader-report-abuse channel S1356 partial / classroom UGC S1671-S1680 deferred). Schema design itself is operator-side.

## Why this exists

S1351 + S1591 share a root: GrimbaNews has **no user-generated content** today. There are no comments to moderate, no votes to brigade-detect, no submissions to triage. The editorial-side surrogate is `/admin/grimba/rss-drafts` (operator approves ingested drafts before publish). To ship UGC safely we need the **moderation queue schema + operator workflow + escalation tiers** defined first. This document defines them.

## Today's surrogate

- `/admin/grimba/rss-drafts` — pre-publish editorial review of ingested content. Per `RssDraftsTest`.
- `news_sources.factuality_score` + `news_sources.credibility_score` — source-level filter at ingest (S1596 partial).
- `App\Support\GrimbaIngestGuardrails` — keyword filter at ingest (S1277 partial).
- `/contact` — reader-side comms (`App\Http\Controllers\GrimbaContactController`). Not a moderation surface.
- `/.well-known/security.txt` — security-side reporting (S1356 partial).

**No user-generated content** = no moderation queue today.

## Proposed UGC content types (when they ship)

1. **Comments** on articles (S1361-S1370 band — deferred).
2. **Reader-submitted tips** (newsroom intake — would gate on partnership program per `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`).
3. **Reader-submitted corrections** (correction-request form on every article — `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` section 5 intake).
4. **Newsletter signup** (already ships — moderation = bot/honeypot filter today, S1353 partial).
5. **Advertiser leads** (already ships — moderation = RateLimiter + honeypot, S1353 partial).

## Schema (S1591 ship target)

```sql
CREATE TABLE moderation_queue (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  content_type VARCHAR(64) NOT NULL,       -- 'comment', 'tip', 'correction_request', 'newsletter_signup', 'advertiser_lead'
  content_id BIGINT NULL,                  -- FK to source table
  content_text TEXT NULL,                  -- denormalized for queue review
  submitter_member_id BIGINT NULL,         -- FK members.id if logged-in
  submitter_ip_hash VARCHAR(64) NULL,      -- sha256(ip + salt) — no raw IP per privacy posture
  submitter_email_hash VARCHAR(64) NULL,   -- for cross-reference dedupe
  severity ENUM('low','medium','high','urgent') DEFAULT 'low',
  status ENUM('pending','approved','rejected','escalated','spam') DEFAULT 'pending',
  auto_classification JSON NULL,           -- spam-score, toxicity-score, source-bias-flag, brand-safety-flag
  reviewer_id BIGINT NULL,                 -- FK members.id (moderator)
  reviewed_at TIMESTAMP NULL,
  rejection_reason VARCHAR(255) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX (status, severity),
  INDEX (content_type, status),
  INDEX (submitter_ip_hash),
  INDEX (submitter_email_hash)
);
```

## Operator workflow

```
[submit] → [auto-classify] → {if low-risk + auto-approve enabled: approve}
                          → {if high-risk: escalate to moderator}
                          → {else: queue for moderator}

[moderator review] → [approve | reject | escalate | mark spam]
```

- **Auto-classify** runs at submit-time:
  - Spam-score (heuristic: link count, repetition, hash collision with known-spam ledger).
  - Toxicity-score (NobuAI second-pass on content_text).
  - Source-bias-flag (per `App\Services\GrimbaBiasClassifier` if applicable).
  - Brand-safety flag (`App\Support\GrimbaIngestGuardrails` keyword reuse).
- **Auto-approve** allowed for low-risk + repeat-trusted-submitter (member with > N approved + 0 rejections).
- **Escalation** to Sara Chen (CISO) for: doxxing attempts, legal-threat language, NCII / CSAM signals (zero-tolerance).
- **Spam** classification routes to permanent-ban list (sha256 of email + IP-hash); future submissions auto-spam.

## UI surface

**Route:** `/admin/grimba/moderation-queue` (new).

**Filters:** status, severity, content_type, date range, reviewer (mine vs all).

**Per-row actions:** Approve (one-click), Reject (requires reason from dropdown), Escalate (selects escalation target).

**Bulk actions:** Approve-all-low-risk (gated to lead moderator role); Mark-spam-all-selected.

**Per-row preview:** Inline content + auto-classification scores + submitter history (count of past approved / rejected).

## Severity tiers + SLA

| Severity | Examples | SLA to first-touch |
|---|---|---|
| urgent | doxxing, legal-threat, NCII signal | 1 hour, page Sara Chen per `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md` P0 |
| high | hate speech, harassment | 4 hours, business-hours |
| medium | spam suspected, off-topic | 24 hours |
| low | clean text, repeat-trusted-submitter | 72 hours (or auto-approve) |

## Brigading detection (S1352 / S1593)

When UGC ships:
- **Anomalous traffic detection** — submission rate spike > 5× baseline per content_type per minute → throttle.
- **IP-hash clustering** — > 5 submissions from same IP-hash within 24h → escalate.
- **Email-hash clustering** — repeated email signups with sequential aliases → spam-flag.
- **Vote-spam guard (S1594)** — gates on vote primitive (which we don't ship today).

## Privacy posture

- **No raw IP stored** — `ip_hash` only (sha256(ip + per-month-rotating-salt)).
- **Email stored as hash** for dedupe; raw email only on confirmed-and-approved content (e.g., newsletter signup).
- **Submitter data retention** — moderator decisions retained 12 months; raw text purged on rejection after 30 days.
- **GDPR data-export honored** per `docs/GRIMBANEWS_GDPR_ROPA.md`.

## Audit + accountability

- **Moderator decisions logged** (`moderation_queue.reviewer_id + reviewed_at + rejection_reason`).
- **Ombudsman review authority** per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` section 4 ("Comment moderation when comments ship").
- **Quarterly audit** of decision patterns — false-rejection rate per moderator.

## Escalation contacts

Per `docs/GRIMBANEWS_ESCALATION_TIERS.md` + `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`:

- **Moderator → lead moderator** — disputed decision.
- **Lead moderator → Sara Chen (CISO)** — safety / legal escalation.
- **Sara Chen → counsel** — legal-threat tier.
- **Sara Chen → Vader** — final ratification of permanent-ban decisions.

## Engineering effort estimate

- Schema + migration: 0.5 sprint.
- Submit-path auto-classification: 2 sprints.
- Queue UI: 3 sprints.
- Auto-approve + auto-spam rules: 1 sprint.
- Brigading detection: 2 sprints.
- Audit logging + ombudsman read-access: 1 sprint.
- Tests + a11y: 1 sprint.
- **Full ship: ~10 sprints, gates on first UGC primitive (comments S1361 or correction-request form).**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1351, S1591; gates on S1361+, S1356)
- Sister docs: `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`, `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`, `docs/GRIMBANEWS_ESCALATION_TIERS.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`
- Existing draft queue: `/admin/grimba/rss-drafts`, `RssDraftsTest`
- Ingest guardrails: `app/Support/GrimbaIngestGuardrails.php`
- Source classifier: `app/Services/GrimbaBiasClassifier.php`
- Contact pipeline: `app/Http/Controllers/GrimbaContactController.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
