# GrimbaNews — Comment Launch Playbook (Per-Cluster Reader-Question Curation Sibling)

**Sprint ID:** S1370
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment launch playbook`
**Walk wave:** BBBB

## Gating dependency

A comment launch playbook needs:

- All S1361-S1369 comment-system sprints shipped (threading, moderation, quality scoring, notification, muting / blocking — all deferred)
- A moderation queue (S1591, deferred)
- CoC + anti-harassment enforcement (S2099, deferred — sibling walk)
- Per-locale catalog for comment UI
- Ad-suppression policy under comments (avoid juxtaposition with toxic content)
- A first-launch cohort decision (full / opt-in / per-author / per-category)

## Surrogate-now infra

- **`/contact` form** — current reader-engagement surface
- **Newsletter feedback** — current async reader-input channel
- **Per-cluster `<x-grimba-dossier-voices>`** — surfaces *editor-curated* alternative perspectives; reader contribution would be a parallel surface
- **Reader-question-of-the-week curation** — sister concept; can be operator-curated today via newsletter without code

## Honest framing

Comments are a high-leverage / high-cost feature. The single biggest commitment is sustained moderation. The playbook itself is editorial + policy work that can be drafted now even though comment code remains deferred.

The per-cluster reader-question curation sibling can ship today as a manual newsletter cadence with zero code — operator picks one cluster per week, asks for reader questions via newsletter, publishes the best ones in the following edition.

## Owners

- **Editorial:** TBD ombudsman + Lucy Leai — moderation policy
- **CMO:** Gary Vaynerchuk — community-engagement strategy
- **Backend:** Rajesh Kumar — comment schema (when S1361-S1369 ship)
- **Frontend:** Nina Patel — comment UI partial
- **CISO:** Sara Chen — abuse / spam guards
- **Compliance:** Maya Patel — UGC liability
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1370 row)
- Comment-system band anchor: `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370`
- CoC enforcement sibling: `docs/GRIMBANEWS_ANTI_HARASSMENT_COC_ENFORCEMENT_PLAN.md`
- Trust-safety moderation scope: `docs/GRIMBANEWS_TRUST_SAFETY_MODERATION_QUEUE_SCOPE.md`
- Dossier-voices pattern: `resources/views/partials/grimba/dossier-voices.blade.php`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
