# GrimbaNews — Referral Code Generation Design

**Sprint ID:** S1952
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral code generation`
**Walk wave:** BBBB

## Gating dependency

Per-member referral codes need:

- `members.referral_code` column (unique, indexed, ~8 chars base32)
- Generator (avoid profanity, avoid look-alikes 0/O 1/I/l)
- Self-serve "regenerate my code" endpoint
- Per-member dashboard surfacing the code (S1957, deferred)
- Migration that backfills existing members

None of this is wired today.

## Surrogate-now infra

- **Per-member email address** — used as the de-facto unique identifier today; UTM tags on share links could attribute manually
- **Share-buttons partial** — `<x-grimba-share-buttons>` already includes copy-link; could be extended to inject `?ref={email_hash}` post-S1952

## Honest framing

Code generation is a 1-day build once the program (S1951) and attribution (S1953) are scoped. Standalone deferred because the schema decision (member-scoped vs anonymous-scoped) is non-trivial.

## Owners

- **Backend:** Rajesh Kumar — schema + generator + endpoint
- **DBA:** Larry Ellison — uniqueness + index strategy
- **Frontend:** Nina Patel — dashboard surfacing
- **CISO:** Sara Chen — abuse-resistant generator (no enumeration)
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1952 row)
- Program design: `docs/GRIMBANEWS_REFERRAL_PROGRAM_TIER_DESIGN.md`
- Attribution: `docs/GRIMBANEWS_REFERRAL_ATTRIBUTION_TRACKING_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
