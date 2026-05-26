# GrimbaNews — Transparency Report Reader Feedback Intake Plan

**Status:** plan v0 (no feedback intake surface for the transparency report itself)
**Owner:** Lucy Leai + Henry Walker on copy + Liam Smith on form UX
**Walks:** Mythos S2017 (Annual transparency report — reader feedback intake) deferred → partial
**Gating dependency:** First annual transparency report published (S2011).

## Why this exists

S2017 lets readers submit feedback on the transparency report — "this number seems wrong", "what about category X?", "consider adding Y". Closes the trust loop.

## Today's surrogate

- Generic `/contact` form. No structured intake.

## Intake form

**Route:** `/transparence/feedback` (FR), `/transparency/feedback` (EN, hreflang sibling)

**Fields:**
- Reader name (optional, signed comments)
- Email (optional, for follow-up)
- Report section (dropdown of report sections — DMCA, corrections, A/B, etc.)
- Feedback (free text, max 2,000 chars)
- "May we cite you in next year's report?" (checkbox)

**Confirmation:** auto-ack within 24h.

## Routing

- All feedback → Lucy + Henry mailing list.
- Lucy reviews quarterly, tags `incorporated` / `noted` / `out_of_scope`.
- Annual report includes "Reader feedback we acted on" section.

## Schema

```sql
CREATE TABLE transparency_feedback (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  report_year SMALLINT NOT NULL,
  section_key VARCHAR(64) NULL,
  feedback TEXT NOT NULL,
  citable BOOLEAN DEFAULT FALSE,
  status ENUM('new','reviewed','incorporated','out_of_scope') DEFAULT 'new',
  created_at TIMESTAMP,
  INDEX (report_year, status)
);
```

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2017)
- Sister docs: `docs/GRIMBANEWS_ANNUAL_TRANSPARENCY_SCOPE_DECISION.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
