# GrimbaNews — Search Suggestion Typeahead Surrogate Plan

**Sprint ID:** S1338
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Search-suggestion typeahead`
**Walk wave:** CCCC

## Gating dependency

Typeahead suggestions need:

- A `/search/suggest?q=` JSON endpoint
- A prefix index (popular saved-searches + popular post titles + topic taxonomy + source roster)
- Frontend debounced fetch (~150ms throttle)
- Rate limiting to prevent abuse (1 req per keystroke)

## Surrogate-now infra

- **`/command-palette.json`** — admin command-palette already serves a typeahead-style JSON list (S580 test pattern)
- **`tests/Feature/SearchFacetsTest`** — locks the search facet contract; typeahead is the autocomplete layer atop
- **`GrimbaEditorialCategories::all()`** — bounded topic list always available for suggestions
- **`GrimbaSourceBreakdown` source list** — bounded source list for suggestions

## Honest framing

Cheap to ship (~4 days incl. frontend). Search volume today is too low to make this a priority — typeahead becomes valuable at scale. Architecturally analogous to the admin command palette already in production.

## Owners

- **Product:** Liam Smith — suggestion ranking policy (recency / popularity / personalization)
- **Frontend:** Nina Patel — debounced input + a11y combobox pattern
- **Backend:** Rajesh Kumar — suggest endpoint + prefix cache
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1338 row)
- Command palette pattern: `docs/GRIMBANEWS_ADMIN_BACKEND_CLOSEOUT_INDEX.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
