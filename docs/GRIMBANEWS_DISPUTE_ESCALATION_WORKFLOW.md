# GrimbaNews — Dispute Escalation Workflow

**Status:** plan v0 (no formal dispute path; operator handles ad-hoc via email)
**Owner:** Lucy Leai (CEO / strategy) on policy + Liam Smith (PM) on workflow + closest-real-role for Ombudsman (deferred S2022 hire)
**Walks:** Mythos S1423 (Dispute escalation) deferred → partial
**Gating dependency:** Ombudsman hire (S2022) + second-eye gate (S1422) + corrections primitive (S2006) — none shipped.

## Why this exists

S1423 codifies what happens when an editorial decision is challenged — by reader, by source, by partner, or by an internal staff disagreement. Today there is no formal path: complaints land on the contact form (`/api/contact`) and are triaged manually. The escalation ladder needs to be defined now so the hire process for Ombudsman (S2022) can reference it.

## Today's surrogate

- **`/api/contact`** + `GrimbaContactController` — single intake for all complaints.
- Operator (Vader) manually triages.

## Escalation ladder (target)

```
Reader complaint
  → Tier 0: Auto-acknowledge (within 24h) — `mailto:editorial@grimbanews.com`
  → Tier 1: Assigned editor reviews + responds (within 7d)
  → Tier 2: Editor-in-chief review if reader unsatisfied (within 14d)
  → Tier 3: Ombudsman independent review (within 30d)
  → Tier 4: External press council referral (per jurisdiction — FR CPPAP, CA QPC, etc.)
```

## Internal-disagreement track

- Editor A vs Editor B on a draft → Editor-in-chief decides. Logged in `editorial_disputes` (new table, deferred).
- Source's right-of-reply request → handled via DMCA/RoR doc workflow (cross-ref).

## Surrogate logging today

- All complaints flow to `contact_messages` table — operator filters manually until `editorial_disputes` lands.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1423)
- Sister docs: `docs/GRIMBANEWS_CROSS_LOCALE_DISPUTE_ROUTING.md`, `docs/GRIMBANEWS_COMPLAINT_TRIAGE_RUBRIC_PLAN.md`, `docs/GRIMBANEWS_OMBUDSMAN_INTAKE_PAGE_SCOPE.md`
- Existing infra: `app/Http/Controllers/GrimbaContactController.php`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
