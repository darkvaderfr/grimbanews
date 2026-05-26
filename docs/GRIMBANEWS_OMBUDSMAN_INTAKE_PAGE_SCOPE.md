# GrimbaNews — Ombudsman Intake Page Scope

**Status:** plan v0 (no `/ombudsman` route; surrogate is `/api/contact`)
**Owner:** Lucy Leai (CEO) + Ombudsman (S2022 — not hired) + Liam Smith (PM) on form UX
**Walks:** Mythos S2023 (Ombudsman intake surface) deferred → partial
**Gating dependency:** Ombudsman hire (S2022) + charter (S2021) + counsel review on intake retention.

## Why this exists

S2023 is the dedicated reader-rights complaint channel. Distinct from generic /contact because it has stronger confidentiality posture and triggers the structured ombudsman workflow (S2026 rubric).

## Today's surrogate

- `/api/contact` + `GrimbaContactController` — generic intake, no ombudsman-specific routing.

## Route + form

**Route:** `/ombudsman` (FR primary), `/ombudsman` EN sibling

**Fields:**
- Complainant identity (name + email, or "anonymous")
- Subject line
- Subject article / cluster URL (optional)
- Complaint nature (dropdown: editorial bias, factual error, harm, conflict-of-interest, other)
- Free-text complaint (max 5,000 chars)
- Evidence attachments (PDF / image, max 3 files, 5MB each)
- "I consent to publication of an anonymized version in the ombudsman annual report" (checkbox)

## Confidentiality posture

- All submissions encrypted at rest with key held by Ombudsman role.
- Complainant identity NEVER shared without explicit consent.
- Retention: 7 years (counsel default; reviewable per jurisdiction).

## Anti-abuse

- Rate-limit: 5 submissions / day per IP / email.
- captcha on form.
- Operator can block bad-faith complainants (with audit trail).

## SLA copy on page

- "Initial acknowledgment within 14 days, decision within 60 days, per S2028."
- Public-rights summary linked to `/vos-droits` (S2036).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2023)
- Sister docs: `docs/GRIMBANEWS_OMBUDSMAN_EMAIL_ALIAS_PROVISIONING.md`, `docs/GRIMBANEWS_COMPLAINT_TRIAGE_RUBRIC_PLAN.md`, `docs/GRIMBANEWS_INVESTIGATION_LOG_SCHEMA.md`, `docs/GRIMBANEWS_COMPLAINT_PUBLIC_FINDINGS_PUBLICATION_PLAN.md`, `docs/GRIMBANEWS_VOS_DROITS_READER_RIGHTS_PAGE_SCOPE.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
