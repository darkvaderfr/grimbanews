# GrimbaNews — Annual Transparency Report Scope

**Status:** scope v0 (no transparency report shipped; first annual cycle pending)
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO) on counts + Vader on publish authority
**Walks:** Mythos S1359 (transparency report — publisher-level) + S1599 (trust & safety transparency report) deferred → partial
**Gating dependency:** First full publication year of operations under the GrimbaNews brand + DMCA / right-of-reply policy live (per `docs/GRIMBANEWS_DMCA_RIGHT_OF_REPLY_POLICY.md`). Scope itself is operator-side.

## Why this exists

S1359 + S1599 share a root: no annual transparency report has been published yet, and no scope statement exists for what it should contain. Most peer publishers (Guardian Open Journalism, Reuters Trust, Nieman Lab) publish annual reports with mostly-similar buckets. This document defines the GrimbaNews scope so the first annual report is a fill-in-the-numbers exercise, not a fresh-design pass.

## Publication cadence

- **Annual report** — published every Q1 covering prior calendar year.
- **Quarterly metrics roll-up** — internal only, fed by automation ledger.
- **Ad-hoc disclosures** — when significant event (major takedown, court order) requires disclosure within 90 days.

## Report sections

### 1. Editorial accountability

- **Corrections issued** — count by editorial_category, by editorial_region. Source: `posts.corrections` JSON (gates on S2006 corrections primitive).
- **Right-of-reply requests received / honored / refused** — counts by jurisdiction. Source: `legal_takedowns` table per `docs/GRIMBANEWS_DMCA_RIGHT_OF_REPLY_POLICY.md`.
- **Ombudsman investigations opened / closed** — counts by severity. Source: `ombudsman_investigations` table (gates on S2027 deferred).
- **Editorial-policy revisions** — count + summary. Source: git history of `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md` + `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`.

### 2. Source diversity

- **Total sources active** — Source: `news_sources` where `active=true`.
- **Source breakdown by region** — Source: `news_sources.country` aggregation.
- **Source breakdown by bias band** — Source: `news_sources.bias_rating` aggregation. Honest reporting includes the "unknown" bucket.
- **Source credibility distribution** — histogram of `news_sources.credibility_score`.
- **Sources added / removed** during the year — count + reasoning summary.

### 3. Content distribution

- **Articles ingested** — total + per region + per category.
- **Articles published (in-house) vs aggregated** — count.
- **Articles depublished** — count + reason buckets (DMCA, right-of-reply, court order, partner request, editorial decision, factual error).
- **Cluster engine actions** — clusters created, merged, split.

### 4. Trust & safety

- **DMCA notices received / actioned / counter-noticed** — by jurisdiction. Source: `legal_takedowns`.
- **Court orders received** — count + jurisdiction. Aggregate disclosure only.
- **Reader-reported abuse** — count by category. Source: `moderation_queue` per `docs/GRIMBANEWS_MODERATION_QUEUE_DESIGN.md`.
- **Comment moderation actions** (when comments ship per S1361+) — approved / rejected / spam counts.
- **Source delistings** — count + reasoning.

### 5. Security + availability

- **Uptime** — % availability. Source: `grimba_automation_runs` aggregated.
- **Security incidents** — count by severity. Source: incident ledger per `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`.
- **DR drill outcomes** — pass / fail count + remediation summary. Source: `docs/GRIMBANEWS_DR_DRILL_*.md` series.
- **Bug-bounty disclosures** — count + severity summary if program ships per `docs/GRIMBANEWS_BUG_BOUNTY_SCOPE.md`.

### 6. Privacy

- **GDPR data-export requests received / honored** — count.
- **GDPR erasure requests received / honored** — count.
- **Cookie-consent breakdown** — % accept / reject / partial. Source: `grimba_consent` cookie aggregate (anonymized).
- **Data breaches (if any)** — disclosed per regulatory deadlines.

### 7. Aid + government requests

- **Government data requests** — count by jurisdiction + type. Aggregate-only.
- **Subpoenas** — count + compliance / refusal. Counsel-reviewed before disclosure.

### 8. Diversity + governance

- **Editorial roster size + composition** — anonymized count + role mix.
- **Partner roster size** — per `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`.
- **Vendor changes** — per `docs/GRIMBANEWS_VENDOR_REGISTER.md` quarterly review.
- **Editorial board / advisory board** (if formed) — composition.

### 9. Audits + verifications

- **SOC 2 status** — current. Source: `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md`.
- **External audits** — IFCN (S2148 deferred), Trust Project (S2144 deferred), press council standing (per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` section 8).
- **Public methodology revisions** — per `docs/GRIMBANEWS_SOURCE_CLASSIFICATION_METHODOLOGY.md` git history.

### 10. Financial transparency (if structured as non-profit / public-trust)

- **Revenue sources** — % from subscriptions / ads / partnerships / donations.
- **Cost breakdown** — Ray Dalio review.
- Optional if structured as for-profit; some peers publish anyway for credibility.

## Reporting infrastructure

- **Automation ledger** — `grimba_automation_runs` already captures per-job outcomes.
- **Per-section aggregation queries** — defined in `app/Console/Commands/GrimbaTransparencyMetrics.php` (new). Outputs JSON snapshot per quarter.
- **Annual report template** — Markdown source-of-truth at `docs/transparency/transparency-report-{year}.md` (directory does not yet exist; create on first report).
- **Public surface** — `/transparence` (FR primary) + `/transparency` (EN). Gates on first report ready.

## Editorial review

- Drafted by Lucy Leai.
- Counts verified by Sara Chen (security) + Larry Ellison (data) + Ray Dalio (financial section).
- Reviewed by ombudsman per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` section 4 (editorial accountability of the report itself).
- Final sign-off: Vader.

## Privacy posture in report

- **No PII** in published report.
- **Per-user counts only when N > 30** to prevent re-identification of small jurisdictions.
- **Vendor names** disclosed only with vendor consent (per `docs/GRIMBANEWS_VENDOR_REGISTER.md` confidentiality clauses).

## Engineering effort estimate

- Aggregation command + per-quarter JSON snapshot: 2 sprints.
- Per-section template renderer: 2 sprints.
- Public route + page: 1 sprint.
- Annual cadence + reminder cron: 0.5 sprint.
- Counsel + ombudsman review-cycle workflow: 1 sprint.
- **Full ship: ~6-7 sprints, then annual cadence.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1359, S1599; cross-refs S1356-S1358, S1591-S1600, S2001+)
- Sister docs: `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`, `docs/GRIMBANEWS_DMCA_RIGHT_OF_REPLY_POLICY.md`, `docs/GRIMBANEWS_MODERATION_QUEUE_DESIGN.md`, `docs/GRIMBANEWS_SOURCE_CLASSIFICATION_METHODOLOGY.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md`, `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`, `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`
- Ledger source: `grimba_automation_runs` table
- DR drill series: `docs/GRIMBANEWS_DR_DRILL_*.md`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
