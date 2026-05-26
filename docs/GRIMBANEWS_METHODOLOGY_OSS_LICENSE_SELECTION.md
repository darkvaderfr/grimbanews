# GrimbaNews — Methodology OSS License Selection

**Status:** plan v0 (no public OSS repo today; darkvaderfr private only)
**Owner:** Vader (final license authority per CLAUDE.md global policy) + counsel + Lucy Leai
**Walks:** Mythos S2042 (Methodology repo — license selection) deferred → partial
**Gating dependency:** OSS scope decision (S2041) + counsel pass.

## Why this exists

S2042 picks the license. Decision drives everything else: contributor expectations, downstream use, sponsorship eligibility, academic adoption.

## Today's surrogate

- All methodology lives in private `darkvaderfr` repo. Not OSS.

## Candidate licenses

| License | Type | Pros | Cons | Fit |
|---|---|---|---|---|
| MIT | permissive | Maximum reach, simple, widely understood | No copyleft — any fork can stay closed | Default for libraries |
| Apache 2.0 | permissive | Patent grant, attribution clarity, well-vetted | Slightly longer than MIT | Good for institutional adopters |
| CC-BY 4.0 | content | Designed for documentation / data | Not ideal for code | Documentation-only sub-repos |
| GPL v3 | copyleft | Forces downstream openness | Discourages commercial adopters | Mission-aligned if anti-commercial |
| AGPL v3 | strong copyleft | Closes SaaS loophole | Most restrictive for adopters | Last resort |

## Decision rubric

| Criterion | Weight | MIT | Apache 2.0 | CC-BY |
|---|---|---|---|---|
| Academic adoption ease | 0.30 | high | high | high |
| Commercial-friendly | 0.20 | high | high | n/a (content) |
| Contributor confidence (patent grant) | 0.20 | low | high | n/a |
| Brevity / simplicity | 0.10 | high | medium | medium |
| Cross-repo polyglot fit | 0.10 | high | high | n/a |
| Mission alignment | 0.10 | medium | medium | high (for docs) |

## Preliminary recommendation

- **Code repos:** Apache 2.0 (patent grant matters; institutional adopters prefer it).
- **Documentation / methodology text:** CC-BY 4.0.
- **Datasets (if any):** CC-BY-SA 4.0 (share-alike preserves transparency).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2042)
- Sister docs: `docs/GRIMBANEWS_METHODOLOGY_CLUSTER_MERGE_EXTRACTION.md`, `docs/GRIMBANEWS_METHODOLOGY_DEDUP_RULES_EXTRACTION.md`, `docs/GRIMBANEWS_OSS_CODE_OF_CONDUCT_DECISION.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
