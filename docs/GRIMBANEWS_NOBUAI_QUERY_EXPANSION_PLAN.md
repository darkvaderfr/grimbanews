# GrimbaNews — NobuAI Query Expansion Surrogate Plan

**Sprint ID:** S1336
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — NobuAI query expansion`
**Walk wave:** CCCC

## Gating dependency

NobuAI query expansion ("immigration france" → "immigration France OQTF Cimade asylum réfugiés") needs:

- NobuAI driver wired into the search path (the chain exists per S1071 partial)
- A cache layer (expand same query at most 1x/day to control LLM cost)
- A guard against query injection / prompt injection
- A "show what was added" affordance for transparency

## Surrogate-now infra

- **`GrimbaNobuAi::failoverOrder()`** — driver chain ready to call
- **`GrimbaSavedSearches::matchingPosts()`** — query path that would accept expanded terms
- **`tests/Feature/GrimbaNobuAiBrandPurityTest`** — proof of NobuAI-as-classifier discipline

## Honest framing

Query expansion is the easiest LLM-search win — improves recall without changing UX. Gates on a cost budget decision (worst case ~$50/month for ~500k unique queries at $0.0001 each with caching).

## Owners

- **Data:** David Chen — expansion-prompt design + cache policy
- **Backend:** Rajesh Kumar — search-path integration
- **Product:** Liam Smith — transparency affordance
- **Security:** Maya Patel — prompt-injection guard
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1336 row)
- Semantic search: `docs/GRIMBANEWS_SEMANTIC_SEARCH_QUERY_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
