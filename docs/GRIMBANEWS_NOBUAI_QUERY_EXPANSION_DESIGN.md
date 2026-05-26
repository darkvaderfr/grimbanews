# GrimbaNews — NobuAI Query Expansion Design

**Status:** plan v0 (no query expansion runs; queries hit FTS5 verbatim)
**Owner:** David Chen (Data Scientist) on prompt template + Rajesh Kumar (Backend) on integration + Elon Musk on cost guardrails
**Walks:** Mythos S1466 (NobuAI query expansion) deferred → partial
**Gating dependency:** Embedding store (S1076) not required — but query expansion most useful with semantic channel (S1464).

## Why this exists

S1466 takes a query like "trump tariff" and expands it to ["trump tariff", "donald trump tariffs", "trade war", "trump administration trade policy"] for richer retrieval. Per NobuAI branding policy, all user-facing surfaces call this "NobuAI search expansion" — provider name never surfaces.

## Today's surrogate

- No expansion. FTS5 MATCH() handles only literal stem matches.

## Expansion strategy

```
query: "trump tariff"
  ──► NobuAI driver prompt:
        "Given the search query '{q}' in locale '{locale}',
         return up to 5 paraphrases / synonyms / related terms
         as a JSON array. No commentary."
  ──► response: ["trump tariffs", "donald trump trade policy",
                  "trump trade war", "trump import duties"]
  ──► retrieval: run each as separate lexical+semantic query
                  → union top-K
                  → RRF merge (per S1465)
```

## Prompt template (live in `app/Support/GrimbaNobuAiPrompts.php` when shipped)

- Token budget: <80 input, <60 output per query
- Cost per query: ~$0.0005 (well under per-search budget)
- Cache key: hash(normalized_query + locale) TTL 24h — same cache as embedding (S1464)

## Locale-aware

- Expansion runs in the reader's locale
- Cross-locale expansion (deferred v2): FR query → optional EN expansions if FR result set is thin

## Fallback

- Expansion call timeout >300ms → drop expansion, use original query alone
- Empty expansion array → use original query alone

## Acceptance gates

- P@5 improvement ≥5% vs no-expansion baseline (on hand-labeled benchmark)
- No regression on exact-match-intent queries (proper nouns, codes)
- Cost <$0.001 per query average

## NobuAI branding compliance

- Reader-visible copy: "Powered by NobuAI search expansion" (when surfaced)
- Admin debug surface: provider name visible (operator gate only)
- Never log provider name to public response headers

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1466)
- Sister docs: `docs/GRIMBANEWS_SEMANTIC_SEARCH_DESIGN_DOC.md`, `docs/GRIMBANEWS_SEMANTIC_SEARCH_HYBRID_MERGE_DESIGN.md`
- Existing infra: `App\Services\GrimbaNobuAi`, `app/Support/GrimbaNobuAiPrompts.php`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
