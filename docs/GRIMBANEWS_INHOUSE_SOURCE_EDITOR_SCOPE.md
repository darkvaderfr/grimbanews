# GrimbaNews — In-House Source Editor Scope

**Status:** scope v0 (no in-house editor today; ingest-only)
**Owner:** Lucy Leai (Strategy) + Steve Jobs (CPO) on editor UX + Sara Kim (QA) on review gating
**Walks:** Mythos S1311 (in-house source editor — scope) deferred → partial
**Gating dependency:** Editorial hire + Botble polymorphic author column wiring + draft-review-publish workflow upgrade (S1317). Scope itself is operator-side.

## Why this exists

S1311 was honest-deferred because GrimbaNews is **ingest-only** today — every `posts` row comes from RSS / news API ingestion. There is no in-house "write your own article" surface. The `posts.author_type` / `posts.author_id` columns exist (Botble polymorphic, referenced at `partials/post.blade.php:1294`) but every aggregated post lands with `author_type=NULL`. To ship in-house editorial we need the **editor scope** decided first: which CMS surface, which workflow, which author identity, which review gate.

## Today's surface

- `/admin/grimba/news-sources` — source-metadata editor (URL / language / country / bias / factuality). Per `tests/Feature/NewsSourcesAdminTest`.
- `/admin/grimba/rss-drafts` — pre-publish draft queue (operator approves / rejects ingested drafts).
- `/admin/posts` — Botble's CMS post editor (works today for any post, but no in-house posts created via it yet).
- `platform/themes/echo/views/author.blade.php` — author profile template exists for Botble-attributed posts; no aggregator post is attributed.

## Proposed in-house editor surface

**Route:** `/admin/grimba/editorial/compose` (new). Distinct from `/admin/posts` to keep ingest-vs-editorial UX separated.

**Capabilities:**

1. **Title + slug + dek + body** — rich-text editor (Botble's existing TinyMCE / CKEditor stack).
2. **Author attribution** — required field; selects from `members` table where `role='editorial'` (gates on S1315 editorial role).
3. **Editorial region** — `posts.editorial_region` ∈ {africa, international, dom-tom} (required).
4. **Category** — `posts.editorial_category` via `App\Services\GrimbaCategoryClassifier` keyword set (operator override).
5. **Hero image** — Botble media library OR external URL via image-proxy (per `docs/PUBLISHER_IMAGE_PROXY_DIAGNOSIS.md`).
6. **NobuAI assist** — "Draft summary from outline" button → NobuAI 3-bullet summary. Operator edits before save.
7. **Source citations** — multi-row picker for `news_sources` (sources cited). Stored in `posts.source_citations` JSON column (new — gates on S1313 byline).
8. **Draft → review → publish** workflow — gates on S1317.

## Workflow

```
[Compose] → [Draft] → [Review by 2nd editorial] → [Publish] → [Live]
                ↓                  ↓
            [Save & exit]    [Send back with notes]
```

- **Draft** — `posts.status = 'draft'`. Only author + reviewers can see.
- **Review** — assignee per S1318 deferred (operator-side: ping the on-deck reviewer).
- **Publish** — `posts.status = 'published'`. Triggers cluster-engine (existing pipeline at `App\Console\Commands\GrimbaClusterStories`).

## Author identity

- `members.role` extended: `'editorial'`, `'editor-in-chief'`, `'ombudsman'` (per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`).
- Author profile fields: name, photo, bio, expertise areas, social handles. Stored on `members` (extend Botble member model).
- Public author page route already exists at `platform/themes/echo/views/author.blade.php` — wire to in-house posts via `posts.author_type = MemberAccount::class`.

## Style guide enforcement (S1319 stub)

- Editorial style guide lives at `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md` (deferred — operator-side).
- Pre-publish lint check: required-field validation (dek, hero, citation count ≥ 2 except editorials).
- NobuAI second-pass: "Run grammar + house-style check" button (advisory, not blocking).

## Editorial categories

In-house posts can also tag as **non-aggregated content types**:

- **News explainer** (writes from aggregated cluster + adds context).
- **Analysis** (opinion-labeled).
- **Investigation** (multi-source long-form).
- **Editorial** (publisher-voice; flagged on render).
- **Newsletter / curated digest** (editor-curated; gates on S1290 monetization).

Flag rendered via `partials/byline.blade.php` (new) so readers see attribution clearly.

## Bias self-disclosure

In-house pieces still get scanned by `App\Services\GrimbaBiasClassifier` to position on the bias bar. Author can pre-declare bias category for transparency; classifier confirms.

## Ombudsman scope

Per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` section 4, in-house editorial decisions are **in scope** for ombudsman review. Editor must keep source materials accessible per `posts.source_citations` JSON.

## Engineering effort estimate

- Compose surface: 4 sprints (rich text + media + citations picker + NobuAI assist).
- Workflow state machine (draft → review → publish): 2 sprints.
- Author identity extension: 1 sprint.
- Public author page wiring: 1 sprint.
- Style-guide lint check: 1 sprint.
- Tests + a11y pass + reskin: 2 sprints.
- **Full ship: ~12 sprints once editorial roster hired.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1311; S1312-S1320 dependencies)
- Sister doc: `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` (governance of in-house editorial)
- Sister doc: `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md` (editorial direction)
- Sister doc: `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md` (paid-tier dependencies on in-house content)
- Existing admin surfaces: `/admin/grimba/news-sources`, `/admin/grimba/rss-drafts`, `/admin/posts`
- Author template: `platform/themes/echo/views/author.blade.php`
- Classifier: `app/Services/GrimbaCategoryClassifier.php`, `app/Services/GrimbaBiasClassifier.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md` (editorial hires deferred)
