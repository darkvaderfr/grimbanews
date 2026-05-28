# GrimbaNews — External Audit Kickoff Plan

**Status:** plan v0
**Owner:** Sara Chen (CISO) — SPOC for the external auditor
**Walks:** Mythos S1893 (external-audit kickoff) deferred → partial
**Gating dependency:** Signed engagement letter (Wave SUB-55, S1892).

## Why this exists

The kickoff sets the tone for the entire audit cycle. A disorganized kickoff costs days of fieldwork rework. A well-prepped kickoff lets the auditor hit the ground running and signals to them that controls are mature, which reduces sampling depth and shortens the engagement.

## Per-kickoff agenda (1.5-hour session)

1. Introductions (5 min) — engagement partner, manager, senior, staff from auditor side; CISO, CEO, sponsor from our side.
2. Engagement-letter walkthrough (15 min) — confirm scope, period, deliverables, timeline.
3. Control universe walkthrough (30 min) — Sara Chen walks the auditor through our SOC 2 / ISO 27001 controls register.
4. Evidence-portal setup (10 min) — auditor's secure portal + our SPOC access.
5. Evidence-request-list (PBC) intake (15 min) — auditor delivers their "Provided By Client" list.
6. Schedule (10 min) — interview windows, fieldwork dates, draft-report milestone.
7. Communication norms (5 min) — weekly status meeting time, ad-hoc question SLA.

## Per-kickoff prep checklist (Sara Chen, T-7 days)

- [ ] Control universe register exported to current state (latest commits referenced).
- [ ] Evidence library landing pages reviewed for stale links.
- [ ] Org chart updated; auditor will reference it for role-based interviews.
- [ ] Vendor register snapshot taken (per SUB-50 / SUB-51 docs).
- [ ] Risk register snapshot taken.
- [ ] Incident register snapshot for the audit period.
- [ ] Internal audit findings register snapshot (per SUB-53, S1885).
- [ ] Change-management log snapshot for the audit period.
- [ ] Management review minutes for the audit period stitched together.
- [ ] Pre-read packet sent to engagement partner T-3 days.

## Per-PBC list response

The auditor's PBC (Provided By Client) list typically has 50-150 items. We commit to:
- Per-item triage within 2 business days.
- Per-item delivery within engagement-letter-specified turnaround (typically 5-10 days).
- Per-item version (so any later changes during fieldwork are tracked).

## Per-kickoff outputs

After the meeting:
- Per-meeting minutes drafted by CISO within 2 business days.
- Per-meeting decisions logged in `/admin/grimba/external-audit/YYYY-engagement/`.
- Per-meeting follow-up tasks assigned in our project tracker.

## Per-kickoff red flags

If during kickoff:
- Auditor seems unfamiliar with our industry → escalate to engagement partner.
- Auditor scope interpretation diverges from engagement letter → freeze and re-baseline.
- Auditor SPOC repeatedly changes → request stable engagement team.

## Cross-references

Master plan: S1893. Sister: `docs/GRIMBANEWS_EXTERNAL_AUDIT_FIRM_ENGAGEMENT.md` (S1892), `docs/GRIMBANEWS_EXTERNAL_AUDIT_FIELDWORK.md` (S1894 next).
