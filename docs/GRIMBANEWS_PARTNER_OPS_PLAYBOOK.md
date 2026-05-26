# GrimbaNews — Partner Operations Playbook

**Status:** plan v0 (no partner program in operation)
**Owner:** Liam Smith (PM) chairs program + Victor Garcia (Business Development) on partner outreach + Emma Brown (Customer Success) on onboarding + Michael O'Connor on partner-facing docs
**Walks:** Mythos S1187 (Partner playbook) deferred → partial
**Gating dependency:** API v2 + sandbox + docs shipped + first commercial-partner contract

## Why this exists

S1187 is the operator-side runbook for the entire partner lifecycle — sourcing → onboarding → renewal → offboarding. Without it, ad-hoc partner work eats senior-team time + creates contract inconsistencies.

## Today's surrogate

- **`GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`** — content-share partnership pattern (Wave LLL).
- **`GRIMBANEWS_SYNDICATION_AGREEMENT_TEMPLATE.md`** — legal template (Wave LLL).
- **`GRIMBANEWS_PARTNERSHIP_PROGRAM_LAUNCH_PLAYBOOK.md`** — broader partnership launch (Wave LLL).

This S1187 doc covers the **API-specific** partner lifecycle (different from newsroom content partnerships).

## Partner lifecycle

### Stage 1 — Sourcing (Victor Garcia)

- Targeted outbound to research labs, fintech, government open-data orgs, academic NLP groups.
- Inbound from `api@grimbanews.com` form.
- Qualification: use case clarity + scale fit + license fit.

### Stage 2 — Discovery call

- 30-min call: Victor + Liam.
- Cover: use case, expected request volume, integration timeline, contract terms preference.
- Deliverable: sandbox key issuance + technical doc share.

### Stage 3 — Sandbox integration

- Partner builds against sandbox using docs.
- Slack channel for technical questions (Hannah Kim, Rajesh Kumar on call).
- Success metric: end-to-end working integration in <14 days.

### Stage 4 — Contract

- Lucy Leai / Ray Dalio sign off on commercial terms.
- Standard term: 12 months auto-renew, 30-day cancellation.
- Custom terms: per partner — escalate to legal review.

### Stage 5 — Production rollout

- Production key issued.
- Slack channel renamed `#partner-<partner-slug>`.
- Per-week metric review for first 4 weeks.

### Stage 6 — Steady state

- Monthly invoice (Ray Dalio).
- Quarterly business review.
- Annual contract renewal motion.

### Stage 7 — Offboarding

- 30-day notice.
- Final invoice.
- Key revocation (per S1184).
- Slack channel archived.

## Partner-facing SLA (per S1189)

- Uptime: 99.5% monthly (excludes scheduled maintenance announced 7d in advance).
- p95 latency: <500ms (per endpoint baseline).
- Support response: <24h business days (Liam Smith primary).

## Incident comms

- Per-partner incident: email partner contact + Slack DM.
- Sub-1% impact: post-mortem within 48h.
- Major incident (>10% partners affected): status page entry + email blast.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1187)
- Sister docs: `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`, `docs/GRIMBANEWS_SYNDICATION_AGREEMENT_TEMPLATE.md`, `docs/GRIMBANEWS_PARTNERSHIP_PROGRAM_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_PARTNER_DOCS_PLAN.md`, `docs/GRIMBANEWS_API_SLA_DESIGN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
