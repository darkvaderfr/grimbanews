# GrimbaNews — NobuAI Reader Trust Score Surrogate Design

**Sprint ID:** S1088
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI reader trust score`
**Walk wave:** BBBB

## Gating dependency

A per-summary trust score requires:

1. Reader feedback channel (S1089 thumbs UI) — itself deferred
2. Per-summary version history (S1229 deferred — every regenerate overwrites `posts.summary_nobuai`)
3. An aggregator job (`grimba:nobuai-rollup-trust`) reading `nobuai_feedback` + producing `posts.summary_nobuai_trust_score`
4. A surfacing rule for thresholds (suppress below X, badge above Y, escalate to editor below Z)

None of those four exist as code today. Cross-reference: S1088 is explicitly gated on S1089 per the master plan row text.

## Surrogate-now infra

What approximates a trust signal today:

- **Per-cluster bias-distribution badge** — `app/Support/GrimbaSourceBreakdown.php` is the closest live signal of "this summary is grounded in N sources across M perspectives"
- **Per-source factuality_score** — `news_sources.factuality_score` (set via curated ingest) acts as an upstream trust prior
- **Provider-name brand-lock test** — `tests/Feature/GrimbaNobuAiBrandLockTest.php` enforces that no provider-name leak (Anthropic / OpenAI / etc.) ever ships to a reader — this is the only *automated* trust gate that runs today on every commit
- **Methodology page disclosure** — `/methodologie` declares the trust contract publicly

## Honest "partial" framing

A trust score with no closed feedback loop (S1089) is a number with no input. Surrogating the trust signal via source-breakdown + factuality-score is what we can defend today; a per-summary score gates on the full S1088-S1089-S1229 stack.

## Owners

- **Data Science:** David Chen — aggregation model + threshold tuning
- **Backend:** Rajesh Kumar — `grimba:nobuai-rollup-trust` job + ledger schema
- **Product:** Liam Smith — trust-score surfacing rules
- **CISO:** Sara Chen — abuse-resistant scoring
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1088 row)
- Feedback-loop gate: `docs/GRIMBANEWS_NOBUAI_READER_FEEDBACK_THUMBS_DESIGN.md`
- Mythos anchor: `docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
