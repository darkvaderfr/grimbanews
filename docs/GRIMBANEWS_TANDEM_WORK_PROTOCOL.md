# GrimbaNews Tandem Work Protocol

**Status:** active operating rule  
**Created:** 2026-04-29  
**Scope:** all contributors, agents, and review lanes working on GrimbaNews pre-production  

This protocol keeps the GrimbaNews team moving in tandem across the pre-production sprint registry. It extends the operating rules in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`.

## Prime Directive

All hands stay on deck. When a lane is unblocked, contributors continue executing atomic sprint outcomes, verifying completed work, or documenting release evidence. Idle time should turn into QA, docs, risk reduction, or blocker removal.

## How To Pick Work

1. Read `memory.md`.
2. Check `git status --short` and protect unrelated work.
3. Open `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`.
4. Pick the next unblocked sprint outcome that matches your lane.
5. Confirm the acceptance evidence needed before editing.
6. Keep the change small enough to review and test.

## Tandem Lane Rules

| Lane | Keep moving by |
|---|---|
| Product and design | Tightening reader/admin flows, interaction states, empty states, and release signoff criteria. |
| Frontend and reader UX | Shipping visual polish, responsive fixes, accessibility, dark/light mode, and route-level smoke coverage. |
| Backend and platform | Hardening ingest, publishing automation, commands, scheduler contracts, data integrity, and admin controls. |
| Data and intelligence | Improving NobuAI, translation, clustering, source intelligence, metadata confidence, and failure handling. |
| QA and release | Running focused tests, browser smoke, visual baselines, release evidence, rollback checks, and risk triage. |
| Security and privacy | Verifying auth, CSRF, secret redaction, cookies, admin-only data, consent, retention, and legal surfaces. |
| Editorial and growth | Refining source strategy, newsletter value, launch messaging, regional editions, and audience loops. |
| Revenue and operations | Hardening ads, subscriber value, analytics, cost controls, support paths, and launch monitoring. |
| Documentation and support | Keeping runbooks, closeouts, handoffs, evidence templates, and support docs current. |

## Evidence Required

Every completed sprint outcome should leave:

- Sprint ID and outcome title.
- Files changed.
- Verification command or manual smoke result.
- Screenshots or browser evidence when UI changed.
- Known risks and follow-up dependency.
- Commit SHA after commit, when committed.

## Coordination Rules

- One contributor owns a file or module while editing it; others should choose separate files when possible.
- Shared contracts require extra care: routes, migrations, scheduler commands, environment variables, auth, provider config, and public CSS/JS need a quick impact note.
- If two lanes depend on the same contract, write the contract down before changing both sides.
- If blocked, document the exact blocker and move to verification, docs, or another unblocked sprint.
- Do not modify `CLAUDE.md` unless the current task explicitly requires it; it may contain local governance changes in progress.

## Release Discipline

- No production deployment until release gates G1-G10 are green.
- No public provider names; reader-facing AI is `NobuAI`.
- Public pages must pass mobile, desktop, light mode, dark mode, and incognito checks before signoff.
- Admin actions should be controls and diagnostics, not daily manual requirements.
- Automation, scheduler, and ingest changes need repeatable command evidence.

## Handoff Format

Use this compact handoff after each sprint or work block:

```text
Sprint:
Outcome:
Files:
Verification:
Risks:
Next:
Commit:
```

The goal is continuous, coordinated pre-production progress without losing evidence or stepping on another contributor's work.
