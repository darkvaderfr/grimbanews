# GrimbaNews — Search Filter by Topic / Category Plan

**Status:** plan v0 (no `?category=` filter in searchHandler; per-category pages are the surrogate)
**Owner:** Liam Smith (PM) on UX + Nina Patel (Lead Frontend) on filter UI + Rajesh Kumar (Backend) on query
**Walks:** Mythos S1475 (Search filter — by topic / category) deferred → partial
**Gating dependency:** Existing FTS5 search + Botble `posts ↔ categories` pivot.

## Why this exists

S1475 lets a reader narrow `/search?q=climate` to a specific category (Politique, Sciences, etc.). Today the only category-narrow path is to visit `/categorie/{slug}` directly — no combination of query + category.

## Today's surrogate

- `/categorie/{slug}` per-category pages.
- `/search?q={q}` with no category filter.

## Spec (target)

```
GET /search?q=climate&category=sciences&category=politique
```

- Multi-value `category` parameter (array)
- Server enforces whitelist via `GrimbaEditorialCategories::all()` slugs
- Empty / unknown slug → silently dropped (no error)
- AND across categories (post must belong to all selected) — initial choice; flip to OR if UX testing prefers

## UI surface

- Sidebar facet panel on `/search` results page
- Checkbox per category with live count badge (e.g., "Sciences (47)")
- Counts come from current result set, not corpus-wide
- "Effacer les filtres" / "Clear filters" affordance

## Query plan

```sql
SELECT p.* FROM posts p
WHERE p.id IN (SELECT rowid FROM posts_fts WHERE posts_fts MATCH :q)
  AND p.id IN (
    SELECT post_id FROM post_categories
    WHERE category_id IN (:cat_ids)
    GROUP BY post_id
    HAVING COUNT(DISTINCT category_id) = :cat_count  -- AND semantics
  )
ORDER BY p.published_at DESC
LIMIT 50;
```

## URL encoding

- Server preserves filter state via canonical query string (for /share + back-button)
- `<link rel="canonical">` excludes filter params (avoid duplicate-content SEO penalty)

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1475)
- Sister docs: `docs/GRIMBANEWS_SEMANTIC_SEARCH_DESIGN_DOC.md`, `docs/GRIMBANEWS_TOP_SEARCHES_DASHBOARD_SCOPE.md`
- Existing infra: `app/Support/GrimbaEditorialCategories.php`, `resources/views/categorie/show.blade.php`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
