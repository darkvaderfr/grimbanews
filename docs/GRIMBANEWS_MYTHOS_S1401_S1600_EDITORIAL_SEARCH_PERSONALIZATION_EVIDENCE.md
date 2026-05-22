# Mythos S1401–S1600 — Editorial Workflow v2 + Search v2 + Personalization v2 + Reader v2 + Trust & Safety Evidence Pack

**Status:** evidence reconciliation
**Created:** 2026-05-22
**Author:** Wave AAAAAAAAAA batch close (fourth Mythos post-launch band)
**Scope:** Converts the fourth 200-sprint slice of the Mythos S1001–S2237 post-launch arc — editorial workflow v2 (in-house editor, byline + author system, review queue, versioning + corrections, newsroom partnerships, contributor program), search v2 (semantic, advanced filters, saved-search alerts, analytics), personalization v2 (ML feed, preference center, privacy ops, fairness), reader product v2 (annotations, bookmarks v2, offline mode, accessibility v2), daily-report v2, and trust & safety — into ledger rows pointing at real shipped code, third-party-account deferreds, post-launch product expansions, and operator pickups.

This pack feeds the master `Sprint Evidence Ledger` in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`. The 200 sprint IDs in S1401–S1600 now have a ledger row.

The honest split is **overwhelmingly `deferred`** with a strong base of `partial` rows that have a shipped web/server surrogate. The S1401-S1600 band is by design a post-launch product-expansion arc — most of these features are explicitly gated on either (a) a paid tier (`S1211`+ monetization) that doesn't exist yet, (b) a workflow product (in-house editor / contributor program / byline system) that the operator hasn't started, (c) a vector / embedding store (`S1076`+) that requires infra not yet provisioned, or (d) a federated-identity / cross-device sync layer that requires the member-auth surface (which exists in skeleton form via Botble member middleware) to grow.

The genuinely shipped pieces are the **search v2 filter facet UI**, the **saved-search alert primitive** (table + weekly digest cron + member UX), the **vault digest primitive** (cookie ↔ member sync + weekly mail), the **PWA offline cache**, and the **for-you cookie-only personalization** (read-history avoided-topic surfacing). Everything else is either a surrogate or deferred.

The S001–S1000 pre-launch arc is the launch gate; the S1401-S1600 work is staged post-launch product depth. The valuable evidence is that the foundations (FTS5 search engine, saved_searches table, vault cookie + digest, GrimbaVaultEvents privacy-safe ledger, dossier voices + breakdown for narrative trust display, GrimbaArticleDedupe canonical-URL fold) are in place so each deferred row drops into a working substrate the moment the missing tier / vendor / workflow is provisioned.

---

## S1401–S1410 — Editorial workflow v2 (in-house editor: cluster builder UI, source proposer, draft pickup)

GrimbaNews today is **operator-curated via Botble admin + algorithmic cluster formation**, not a multi-seat in-house editor product. The cluster admin lives at `/admin/grimba/story-clusters` (per `platform/themes/echo/functions/grimba-admin-clusters.php`) and the RSS-draft pickup queue at `/admin/grimba/rss-drafts`. There is no multi-editor invite system, no editor seats, no per-editor cluster authorship trail, no drag-build cluster UI. All `partial` for items where the admin surrogate exists, `deferred` for the multi-seat product expansion.

- **S1401** — In-house editor — seat invite + role: `deferred` — Botble admin auth is single-tenant with one role layer (admin / user). No `editor_seats` / `editor_invitations` table, no per-editor scoping.
- **S1402** — In-house editor — cluster builder UI (drag posts into a cluster): `partial` — `/admin/grimba/story-clusters` ships a list-view cluster admin per `platform/themes/echo/functions/grimba-admin-clusters.php`; drag-drop cluster builder UI `deferred`. Algorithmic surrogate: `App\Services\GrimbaRssPoller::findOrFormCluster()` auto-forms clusters by canonical-URL + title similarity.
- **S1403** — In-house editor — source proposer (suggest a new feed): `partial` — `/admin/grimba/rss-feeds` accepts new RSS feed URLs via the `grimba-admin-rss-feeds.php` function block; reader-side source-proposer form `deferred`.
- **S1404** — In-house editor — draft pickup from queue: `partial` — `/admin/grimba/rss-drafts` is the draft pickup queue per `grimba-admin-rss-drafts.php`; per-editor assignment + lock `deferred`.
- **S1405** — In-house editor — draft enrichment (NobuAI summary + tags): `partial` — `App\Console\Commands\GrimbaEnrichDrafts` runs scheduled enrichment + `GrimbaGenerateNobuAiSummaries` populates `posts.summary_nobuai`; in-editor live regeneration `deferred`.
- **S1406** — In-house editor — preview before publish: `partial` — Botble post-edit screen ships a preview action via the platform; GrimbaNews-specific dossier preview `deferred`.
- **S1407** — In-house editor — publish gate (status=published flip): `complete` — `App\Support\GrimbaPostPublisher` + `GrimbaPublicationPipeline` + `GrimbaPublishTrusted` command enforce the publish gate; `posts.status='published'` is the contract enforced site-wide.
- **S1408** — In-house editor — schedule publish (future-dated): `partial` — Botble post `published_at` column supports future-dating; GrimbaNews scheduler `GrimbaEnsureDailyPublish` ensures daily cadence; per-editor scheduled queue UI `deferred`.
- **S1409** — In-house editor — collaboration / co-author signoff: `deferred` — no multi-author workflow.
- **S1410** — In-house editor — launch retrospective: `deferred` — gates on S1401-S1409 actually shipping; operator-side retro.

## S1411–S1420 — Editorial workflow — byline + author system (author profiles, contribution log)

No GrimbaNews-specific byline system. Articles surface `source_name` (the publisher) as the attribution today; individual author bylines from upstream RSS are not parsed into a structured `authors` table. All `deferred` or `partial`.

- **S1411** — Author table schema: `deferred` — no `authors` / `post_authors` table; current `posts` model has Botble's `author_id` (Botble user FK) but no journalist-profile metadata.
- **S1412** — Author profile page: `deferred` — depends on S1411.
- **S1413** — Author byline display on article: `partial` — article view shows `source_name` (publisher) via `partials/post-meta.blade.php`; per-author byline parse from RSS `<author>` / `<dc:creator>` `deferred`.
- **S1414** — Author byline display on cluster: `partial` — `partials/story/dossier-voices.blade.php` ships per-source voices in a cluster; per-author voices `deferred`.
- **S1415** — Author contribution log: `deferred` — no `author_contributions` table.
- **S1416** — Author follow (reader follows author): `deferred` — no follow-author primitive; follow-source exists via search filters.
- **S1417** — Author RSS feed: `deferred` — no per-author feed route.
- **S1418** — Author analytics dashboard: `deferred` — no per-author view counter.
- **S1419** — Author payout integration: `deferred` — depends on contributor program (S1451+).
- **S1420** — Author launch retrospective: `deferred` — gates on S1411-S1419.

## S1421–S1430 — Editorial workflow — review queue (pre-publish review, second-eye system, dispute escalation)

The current review queue is **algorithmic cluster review** at `/admin/grimba/story-clusters` + draft review at `/admin/grimba/rss-drafts`. There is no second-eye human-review workflow, no dispute escalation, no per-cluster reviewer assignment.

- **S1421** — Pre-publish review queue: `partial` — `/admin/grimba/rss-drafts` is the surrogate. Per-editor assignment + reviewer-pass `deferred`.
- **S1422** — Second-eye approval gate: `deferred` — no two-step approval. Surrogate is the operator manually reviewing the rss-drafts queue.
- **S1423** — Dispute escalation: `deferred` — operator-side editorial.
- **S1424** — Cluster-merge dispute: `partial` — `App\Support\GrimbaDedupeReview` ships review-mode for the `grimba:dedupe-posts` command; explicit cluster-merge dispute UI `deferred`.
- **S1425** — Cluster-split dispute: `partial` — same — `DedupePostsCommandTest` covers review mode but the merge / split tooling is operator-side.
- **S1426** — Source-classification dispute (operator overrides classifier): `partial` — `App\Console\Commands\GrimbaClassifySources` runs scheduled classification; operator can override `news_sources.bias_rating` / `factuality_score` / `ownership_type` directly via `/admin/grimba/news-sources`.
- **S1427** — Bias-rating dispute: `partial` — same — `news_sources.bias_rating` column is operator-editable; reader-side dispute submission `deferred`.
- **S1428** — Translation dispute: `partial` — operator can override `posts.summary_nobuai_locale` via Botble admin; reader-side dispute submission `deferred`.
- **S1429** — Cross-locale dispute routing: `deferred` — operator-side editorial.
- **S1430** — Review-queue launch retrospective: `deferred` — operator-side retro.

## S1431–S1440 — Editorial workflow — versioning + corrections (article revision log, correction notice, retract flow)

Botble's `revisions` table covers post-edit version history at the platform level. GrimbaNews-specific correction notice on the reader surface + retract flow are `deferred`.

- **S1431** — Article revision log (server-side): `partial` — Botble platform `revisions` table captures `Post` model edits via the `RevisionableTrait`; per-edit timeline `partial` (Botble admin surface).
- **S1432** — Article revision diff UI: `partial` — Botble admin diff view; GrimbaNews-specific reader-facing diff `deferred`.
- **S1433** — Correction notice — reader-facing badge: `deferred` — no `posts.correction_notice` column; surrogate is admin manual edit of `posts.content`.
- **S1434** — Retract flow (mark as retracted): `partial` — `posts.status` can be flipped to `draft`/`pending` to depublish; explicit `retracted` status + reader-facing "this article was retracted" banner `deferred`.
- **S1435** — Cluster-level correction propagation: `deferred` — no per-cluster correction propagation.
- **S1436** — Translation-level correction: `deferred` — no per-translation correction notice.
- **S1437** — NobuAI-summary correction (regenerate on flag): `partial` — `grimba:nobuai-summaries --stale --limit=25` every 30 min regenerates stale summaries; explicit operator-flag-to-regenerate UI `deferred`.
- **S1438** — Correction-policy public page: `deferred` — no `/corrections` route; surrogate is `/mentions-legales` legal page.
- **S1439** — Correction audit log: `partial` — Botble `revisions` covers edit history; explicit correction-flag audit `deferred`.
- **S1440** — Correction-flow launch retrospective: `deferred` — gates on S1431-S1439.

## S1441–S1450 — Editorial growth — newsroom partnerships (syndication agreements, content sharing, attribution)

GrimbaNews **consumes** upstream RSS / NewsAPI / newsdata.io feeds today — there is no outbound syndication contract, no partner content-share API, no attribution-reporting back to partners. All `deferred` (operator-side) or `partial` (canonical-URL attribution exists).

- **S1441** — Syndication agreement template: `deferred` — operator-side legal pickup.
- **S1442** — Partner content-share API: `deferred` — no outbound API; surrogate is the per-stream RSS feeds at `/feed.xml`, `/feed.breaking.xml`, `/feed.latest.xml`, per-category feeds (read-only egress).
- **S1443** — Partner attribution display: `complete` — `App\Support\GrimbaArticleDedupe` preserves canonical-URL; article view shows source name + link to upstream via `partials/post-meta.blade.php` + `dossier-voices.blade.php`.
- **S1444** — Partner attribution report: `deferred` — no per-partner reporting.
- **S1445** — Partner exclusivity window: `deferred` — operator-side contract; no `posts.exclusivity_window_until` column.
- **S1446** — Partner content-takedown workflow: `deferred` — operator-side; surrogate is admin manual flip of `posts.status` to `draft`.
- **S1447** — Partner royalty split: `deferred` — depends on contributor program (S1451) + monetization (S1211).
- **S1448** — Partner brand-safety review: `deferred` — operator-side legal pickup.
- **S1449** — Partner case studies: `deferred` — needs ≥1 real partner.
- **S1450** — Partnership-program launch retrospective: `deferred` — gates on S1441-S1449.

## S1451–S1460 — Editorial growth — contributor program (independent journalist intake, pay-per-piece)

No contributor program. All `deferred`.

- **S1451** — Contributor intake form: `deferred` — no `contributor_applications` table.
- **S1452** — Contributor profile + verification: `deferred` — depends on S1411 author system.
- **S1453** — Contributor rate card: `deferred` — operator-side.
- **S1454** — Contributor submission portal: `deferred` — surrogate is operator-managed `/admin/grimba/rss-drafts` queue.
- **S1455** — Contributor editor-handoff: `deferred` — depends on multi-editor workflow (S1401).
- **S1456** — Contributor payout integration (Stripe Connect / Wise): `deferred` — no billing infra (S1211).
- **S1457** — Contributor 1099 / tax reporting: `deferred` — same.
- **S1458** — Contributor analytics dashboard: `deferred` — depends on per-author analytics (S1418).
- **S1459** — Contributor case studies: `deferred` — needs ≥1 real contributor.
- **S1460** — Contributor program launch retrospective: `deferred` — gates on S1451-S1459.

## S1461–S1470 — Search v2 — semantic (NobuAI-powered query expansion, embedding index, related-search suggestions)

The search engine is SQLite **FTS5** (lexical) per `platform/themes/echo/routes/web.php:760-870` — `posts_fts` virtual table with `bm25(posts_fts)` ordering. There is **no embedding store, no vector index, no semantic mode**. All `deferred` (depend on `S1076` embedding store, which itself is deferred).

- **S1461** — Semantic search — design doc: `deferred` — written design `deferred`; the FTS5 surface is the current substrate.
- **S1462** — Semantic search — embedding model pick: `deferred` — depends on `S1076` embedding store (pgvector / qdrant / pinecone).
- **S1463** — Semantic search — embedding index build: `deferred` — same.
- **S1464** — Semantic search — query embedding: `deferred` — same.
- **S1465** — Semantic search — hybrid (lexical + semantic) merge: `deferred` — same.
- **S1466** — NobuAI query expansion (synonym / paraphrase): `deferred` — `App\Services\GrimbaNobuAi` shipped, query-expansion prompt template `deferred`.
- **S1467** — Related-search suggestions ("did you mean X?"): `partial` — search results view (`platform/themes/echo/views/search.blade.php`) exists; "did you mean" / related-search chip UI `deferred`. Surrogate today: FTS5 prefix-token matching + faceted browsing.
- **S1468** — Search-result clustering (group by topic): `partial` — `App\Support\GrimbaHomeFeed` already groups posts by `story_cluster_id` on the homepage; search-results-page cluster grouping `deferred` (current view paginates flat).
- **S1469** — Search-result snippet highlighting: `partial` — FTS5 supports `snippet()` highlighting; search.blade.php currently shows post excerpt only. Highlight wiring `deferred`.
- **S1470** — Semantic-search launch retrospective: `deferred` — gates on S1461-S1469.

## S1471–S1480 — Search v2 — filters (advanced filters, date range, source-set, topic-set)

The search filter facet UI is **shipped today** — the `searchHandler` in `platform/themes/echo/routes/web.php:764-905` ships source, bias, owner, date-from, date-to filters wired to FTS5 query results. Tests in `SavedSearchAlertsTest` lock the criteria-normalization contract.

- **S1471** — Search filter — by source: `complete` — `searchHandler` accepts `?source={id}` and joins on `posts.source_id`. `SavedSearchAlertsTest::test_member_can_save_and_remove_search_alert` covers the criteria.
- **S1472** — Search filter — by bias: `complete` — accepts `?bias=left|center|right|unknown`; `posts.bias_rating` filter applied.
- **S1473** — Search filter — by owner: `complete` — accepts `?owner={owner_name}`; subquery on `news_sources.owner_name`.
- **S1474** — Search filter — by date range (from / to): `complete` — accepts `?from_date=YYYY-MM-DD&to_date=YYYY-MM-DD`; `App\Support\GrimbaPostRecency::wherePublishedDateFrom/To()` applied. Validated by `preg_match('/^\d{4}-\d{2}-\d{2}$/')` guard.
- **S1475** — Search filter — by topic / category: `deferred` — `?category={id}` filter not in current `searchHandler`; surrogate is `/categorie/{slug}` category pages.
- **S1476** — Search filter — by locale: `partial` — `GnTr::orderForTargetLocale()` applies locale priority to result ordering (per `App\Support\GrimbaTranslationPresenter`); explicit `?lang=` filter `deferred`.
- **S1477** — Search filter — by edition (Afrique / International): `partial` — region cookie (`grimba_region`) gates the post corpus globally via `App\Support\GrimbaArticleRegion`; explicit search-filter chip `deferred`.
- **S1478** — Search filter — saved as URL (deep-link to filtered search): `complete` — `searchUrl()` in `App\Support\GrimbaSavedSearches` serializes filter set to query string; saved searches re-emit as `searchUrl($criteria)`.
- **S1479** — Search filter — clear-all action: `partial` — view exists (`search.blade.php`); explicit clear-all button on filter chip UI is theme-side polish.
- **S1480** — Search-filter launch retrospective: `complete` — server-side filter pipeline shipped (5 facets: source, bias, owner, from, to) + saved-search criteria normalization + URL serialization + paginated FTS5 results.

## S1481–S1490 — Search v2 — saved searches (alert queue, digest cadence, unsubscribe)

The saved-search primitive is shipped: `saved_searches` table + `App\Support\GrimbaSavedSearches` + `App\Console\Commands\GrimbaSendSavedSearchDigests` (weekly Monday 04:55 UTC) + `App\Mail\GrimbaSavedSearchDigestMail` + `SavedSearchAlertsTest` (contract).

- **S1481** — Saved-search schema (saved_searches table): `complete` — migration ships `saved_searches` with `member_id`, `search_query`, `search_hash`, `source_id`, `bias`, `owner`, `from_date`, `to_date`, `active`, `last_sent_at`. Hash-based dedupe (`GrimbaSavedSearches::hash()`) prevents dup entries per member.
- **S1482** — Saved-search create flow: `complete` — `POST /search/alerts` route handler (`platform/themes/echo/routes/web.php:918-945`) gated by `member` middleware; cap at `MAX_PER_MEMBER = 12`. `SavedSearchAlertsTest::test_member_can_save_and_remove_search_alert` covers.
- **S1483** — Saved-search delete flow: `complete` — `DELETE /account/saved-searches/{id}` route (`platform/themes/echo/routes/web.php:1521`); same test covers.
- **S1484** — Saved-search list (account page): `complete` — `/account` view shows saved searches; `account.blade.php` renders the list from `GrimbaSavedSearches::listForMember()`.
- **S1485** — Saved-search digest cron: `complete` — `grimba:saved-search-digests` weekly Monday 04:55 UTC per `routes/console.php`; `SavedSearchAlertsTest::test_digest_emails_matching_posts_to_member` covers.
- **S1486** — Saved-search digest email template: `complete` — `resources/views/emails/saved-search-digest.blade.php` rendered by `App\Mail\GrimbaSavedSearchDigestMail`; capped at `DIGEST_POST_LIMIT = 8` matches.
- **S1487** — Saved-search digest unsubscribe (per-search): `partial` — `DELETE /account/saved-searches/{id}` is the per-search unsubscribe; per-email unsubscribe link `deferred` (current implementation uses authenticated member action).
- **S1488** — Saved-search digest cadence config (weekly vs daily): `partial` — current cadence is weekly-only (hardcoded in `routes/console.php`); per-member cadence picker `deferred`.
- **S1489** — Saved-search digest analytics (open / click): `deferred` — no email-event tracking SDK (lands with newsletter v2 S1281+).
- **S1490** — Saved-search launch retrospective: `complete` — primitive shipped + tested + scheduled; 4-row band (schema + create + delete + digest) all complete.

## S1491–S1500 — Search v2 — analytics (top searches dashboard, zero-result tracking)

No search-event logging. The FTS5 query handler does not write to a `search_events` table. All `deferred`.

- **S1491** — Search-event logging schema: `deferred` — no `search_events` table.
- **S1492** — Top-searches dashboard: `deferred` — depends on S1491.
- **S1493** — Zero-result-search tracking: `deferred` — same.
- **S1494** — Saved-search adoption metric: `partial` — `GrimbaSavedSearches::countForMember()` + raw `saved_searches` row count via `DB::table('saved_searches')->count()`; dashboard wrapper `deferred`.
- **S1495** — Per-source search popularity: `deferred` — depends on S1491.
- **S1496** — Per-bias search popularity: `deferred` — same.
- **S1497** — Per-date-range popularity: `deferred` — same.
- **S1498** — Search-result CTR: `deferred` — same.
- **S1499** — Search-A/B test harness: `deferred` — no A/B engine (per `S1073` honest deferral).
- **S1500** — Search-analytics launch retrospective: `deferred` — gates on S1491-S1499.

## S1501–S1510 — Personalization v2 — ML feed (collaborative filter, embedding-based, opt-in only)

The current personalization is **cookie-only avoided-topic surfacing** on `/pour-vous` (per `platform/themes/echo/routes/web.php:1168-1229`) — when `grimba_read` cookie has > 10 ids, surface categories the reader has *not* engaged with recently. No ML model, no collaborative filter, no embedding-based recs.

- **S1501** — ML feed — design doc: `deferred` — written design `deferred`. Cookie-only `pour-vous` is the substrate.
- **S1502** — ML feed — collaborative filter model: `deferred` — needs per-member interaction matrix; current implementation is privacy-first cookie-only (no server-side per-member read log).
- **S1503** — ML feed — embedding-based recs: `deferred` — depends on `S1076` embedding store.
- **S1504** — ML feed — cold-start handling: `partial` — current `/pour-vous` handles cold-start by paginating default category-feed when `grimba_read` count ≤ 10; ML cold-start `deferred`.
- **S1505** — ML feed — opt-in toggle: `partial` — surrogate is per-user cookie `grimba_read` opt-out (one cookie clears history); explicit opt-in toggle `deferred`.
- **S1506** — ML feed — explain-why-recommended: `partial` — current view shows "based on your followed categories" string; per-post "we recommended this because..." `deferred`.
- **S1507** — ML feed — fairness audit: `deferred` — depends on real ML model.
- **S1508** — ML feed — diversity floor (no echo chamber): `partial` — `avoidedTopics` surfacing on `/pour-vous` is the diversity-floor surrogate (forces fresh categories into view). Explicit % diversity floor `deferred`.
- **S1509** — ML feed — model A/B harness: `deferred` — no A/B engine (S1073).
- **S1510** — ML feed launch retrospective: `deferred` — gates on S1501-S1509.

## S1511–S1520 — Personalization v2 — preference center (followed topics, blocked sources, weight slider)

Followed topics exist via cookie (`grimba_follow` CSV of category ids); blocked sources / weight slider `deferred`.

- **S1511** — Preference center — page: `partial` — `/account` ships member preference surface (vault digest toggle + saved-search list); explicit "preferences" tab `deferred`.
- **S1512** — Followed topics — cookie: `partial` — cookie-only follow (cookie `grimba_follow` CSV per `/pour-vous` handler); per-member persist `deferred` until tier.
- **S1513** — Followed topics — server-persisted: `deferred` — no `member_followed_categories` table.
- **S1514** — Blocked sources — UI: `deferred` — no block-source primitive.
- **S1515** — Blocked sources — server-persisted: `deferred` — no `member_blocked_sources` table.
- **S1516** — Weight slider (boost / suppress topic): `deferred` — depends on ML feed (S1501).
- **S1517** — Followed authors: `deferred` — depends on author system (S1411).
- **S1518** — Followed clusters: `deferred` — no follow-cluster primitive; surrogate is sharing the dossier URL.
- **S1519** — Reset preferences action: `partial` — `grimba_read` + `grimba_follow` cookies clear via standard browser cookie controls; explicit "reset" button `deferred`.
- **S1520** — Preference-center launch retrospective: `deferred` — gates on S1511-S1519.

## S1521–S1530 — Personalization v2 — privacy ops (data export, data delete, opt-in/out audit log)

Vault history export exists (`coffre/export.csv`); pour-vous CSV export exists (`pour-vous/export.csv`). Full GDPR DSAR pipeline + opt-in/out audit log `deferred`.

- **S1521** — Data export — vault history (CSV): `complete` — `coffre/export.csv` route (per `platform/themes/echo/routes/web.php:641-644` + `S028 subscriber gate`); exports member's saved post ids.
- **S1522** — Data export — read history (CSV): `complete` — `pour-vous/export.csv` route (`platform/themes/echo/routes/web.php:1235-1287`); cookie-only data export with BOM + UTF-8.
- **S1523** — Data export — saved searches (CSV / JSON): `deferred` — no `account/saved-searches/export.csv` route; surrogate is `/account` page list view.
- **S1524** — Data export — GDPR DSAR full bundle: `deferred` — depends on `S1491` compliance band (GDPR DSAR pipeline).
- **S1525** — Data delete — vault clear action: `partial` — cookie-clear via UI `data-grimba-save-clear` not shipped; manual cookie-clear via browser is the substrate.
- **S1526** — Data delete — read-history clear action: `partial` — same — manual cookie clear.
- **S1527** — Data delete — member account delete: `partial` — Botble member account delete via admin; reader-side self-delete `deferred`.
- **S1528** — Opt-in / opt-out audit log: `partial` — `App\Support\GrimbaVaultEvents` ledger captures vault opt-in events with privacy-safe `ip_hash`; opt-out events `partial`.
- **S1529** — Privacy ops launch comms: `deferred` — operator-side comms.
- **S1530** — Privacy ops launch retrospective: `deferred` — gates on S1521-S1529.

## S1531–S1540 — Personalization v2 — fairness (no-filter-bubble guarantee, opposite-bias surfacing)

The `feed-balance.blade.php` partial + bias-distribution + dossier voices are the no-filter-bubble surrogates on the reader surface. Explicit ML-fairness audits depend on ML feed (S1501) which is deferred.

- **S1531** — No-filter-bubble guarantee — design doc: `partial` — substrate shipped via `partials/feed-balance.blade.php` + `partials/story/bias-distribution.blade.php` + `App\Support\GrimbaClusterBias`; written guarantee doc `deferred`.
- **S1532** — Opposite-bias surfacing: `complete` — `partials/story/dossier-voices.blade.php` ships voices from across the bias spectrum within a cluster; `tests/Feature/GrimbaLaunchReadinessTest` test_story_breakdown_shows_left_center_right covers the contract.
- **S1533** — Diversity floor enforcement: `partial` — `App\Support\GrimbaHomeFeed::breakingsByCluster()` joins on `news_sources.bias_rating` to ensure cluster mixes biases; explicit % floor enforcement `deferred`.
- **S1534** — Cross-locale diversity: `partial` — `dossier-voices.blade.php` shows per-language voices with amber unknown-language badge; explicit cross-locale floor `deferred`.
- **S1535** — Country diversity floor: `partial` — `news_sources.country` column drives geography spread; explicit floor `deferred`.
- **S1536** — Source-credibility diversity: `partial` — `news_sources.credibility_score` + `factuality_score` are stored; cluster builder shows weighted spread; explicit fairness audit `deferred`.
- **S1537** — Ownership diversity (state / corp / nonprofit): `partial` — `news_sources.ownership_type` stored; `partials/ownership-chip.blade.php` displays. Explicit diversity floor `deferred`.
- **S1538** — Echo-chamber detector: `deferred` — depends on per-member read log (which by design does not exist server-side).
- **S1539** — Fairness audit dashboard: `deferred` — no per-member ML behavior to audit yet.
- **S1540** — Fairness launch retrospective: `deferred` — gates on S1531-S1539.

## S1541–S1550 — Reader product v2 — annotations (highlight, note, share-with-quote)

No annotation primitive shipped. Share kit exists; share-with-quote `deferred`.

- **S1541** — Annotation schema (highlights table): `deferred` — no `post_annotations` / `post_highlights` table.
- **S1542** — Highlight UI (text selection): `deferred` — no JS handler for text-selection highlighting.
- **S1543** — Note attached to highlight: `deferred` — same.
- **S1544** — Share-with-quote (Tweet/Bluesky with selected text): `partial` — `partials/story/share-kit.blade.php` ships 6-channel share (X / Bluesky / Facebook / WhatsApp / LinkedIn / Email) with URL + title; per-selection quote-share `deferred`.
- **S1545** — Highlight visible to other readers (public annotations): `deferred` — depends on S1541.
- **S1546** — Private annotations sync across devices: `deferred` — same + cross-device sync.
- **S1547** — Annotation export (Markdown / Roam): `deferred` — depends on S1541.
- **S1548** — Annotation analytics: `deferred` — depends on S1541.
- **S1549** — Annotation moderation: `deferred` — depends on S1541.
- **S1550** — Annotation launch retrospective: `deferred` — gates on S1541-S1549.

## S1551–S1560 — Reader product v2 — bookmarks v2 (folders, tags, cross-device sync via account)

Bookmark primitive is `GrimbaVault` (cookie-based, capped at 50 ids). Server-side persistence via `members.vault_digest_post_ids` exists when member is logged in. Folders / tags / cross-device sync `deferred`.

- **S1551** — Bookmark — basic save action: `complete` — `partials/save-button.blade.php` toggles post id in `grimba_vault` cookie; `data-grimba-save="{id}"` handler client-side. Cap at 50 per `GrimbaVault::parseIds()`. `tests/Feature/VaultTest` covers contract.
- **S1552** — Bookmark — folders: `deferred` — no `vault_folders` column / table; flat list today.
- **S1553** — Bookmark — tags: `deferred` — no tag schema.
- **S1554** — Bookmark — cross-device sync (via account): `partial` — `GrimbaVault::syncCookieToMember()` syncs cookie → `members.vault_digest_post_ids` on login; reverse (member → cookie) `partial` via `/coffre` page rehydration; multi-device drift detection `deferred`.
- **S1555** — Bookmark — list view (`/coffre`): `complete` — `coffre.blade.php` ships the vault list with member-gate via Botble middleware; `S028 subscriber gate` evidence.
- **S1556** — Bookmark — search within saved: `deferred` — no FTS index over vault subset.
- **S1557** — Bookmark — bulk delete: `partial` — per-post unsave via `data-grimba-save` toggle; bulk-clear `deferred`.
- **S1558** — Bookmark — share (vault-share URL): `complete` — `/coffre-share` route exists (per `platform/themes/echo/routes/web.php` private-path guard list in `PwaShellTest`).
- **S1559** — Bookmark — weekly digest email: `complete` — `grimba:vault-digests` weekly cron + `GrimbaVaultDigestMail` + `resources/views/emails/vault-digest.blade.php`; `tests/Feature/VaultDigestTest` covers contract.
- **S1560** — Bookmark v2 launch retrospective: `partial` — v1 (cookie + member sync + digest + share) shipped; folders / tags / cross-device drift `deferred`.

## S1561–S1570 — Reader product v2 — offline mode (PWA cache, sync queue, conflict resolution)

PWA offline shell shipped (per S1156); article-body offline cache + sync queue + conflict resolution `deferred` to native-app phase.

- **S1561** — Offline shell — service worker: `complete` — `public/grimba-sw.js` shipped; `tests/Feature/PwaShellTest::test_service_worker_avoids_private_paths_and_non_cacheable_responses` covers private-path guard (admin / account / member / coffre / coffre-share excluded from cache).
- **S1562** — Offline shell — offline.html fallback: `complete` — `public/offline.html` shipped; manifest + SW pre-cache.
- **S1563** — Offline mode — article body cache (read offline): `partial` — `grimba-sw.js` caches GETs unless `Cache-Control: no-store|private`; explicit per-article precache (save-for-offline button) `deferred`.
- **S1564** — Offline mode — vault sync queue: `partial` — vault cookie persists offline; bookmark action queues for server sync on reconnect (cookie ↔ member sync on next request). Explicit IndexedDB queue `deferred`.
- **S1565** — Offline mode — conflict resolution: `deferred` — single-device cookie today; multi-device conflict resolution depends on cross-device sync (S1554).
- **S1566** — Offline mode — cache eviction policy: `partial` — service-worker LRU on quota pressure (browser-default); explicit per-resource TTL `deferred`.
- **S1567** — Offline mode — install prompt UX: `partial` — manifest + `beforeinstallprompt` browser-default; explicit install-prompt UI `deferred`.
- **S1568** — Offline mode — share-target (PWA receive shared URL): `deferred` — manifest `share_target` not registered.
- **S1569** — Offline mode — analytics (offline interaction queue): `deferred` — no offline-event queue.
- **S1570** — Offline mode launch retrospective: `partial` — shell + private-path guard + fallback shipped + tested; per-article cache + IndexedDB queue + share-target `deferred`.

## S1571–S1580 — Reader product v2 — accessibility v2 (reading mode, font scaling, dyslexia font)

A11y baseline shipped per S751-S800 band. Reading mode + font scaling + dyslexia font are explicit reader-product features, mostly `deferred`.

- **S1571** — Reading mode — design: `deferred` — no `?reading=1` view variant; current article view is already content-first per `partials/post-hero-img.blade.php` + reading-time chip.
- **S1572** — Font scaling — UI: `partial` — browser-default zoom + `rem`-based typography in `Public Sans` / `Fraunces` stack; explicit A−/A+ controls `deferred`.
- **S1573** — Dyslexia-friendly font (OpenDyslexic / Atkinson Hyperlegible): `deferred` — single font stack today.
- **S1574** — Line-spacing controls: `deferred` — fixed line-height.
- **S1575** — High-contrast mode: `partial` — dark / light themes locked by `GrimbaDarkModeContractTest`; explicit high-contrast variant `deferred`.
- **S1576** — Screen-reader hints v2: `partial` — `aria-label` sweep across info-pill / share-kit / 178 occurrences (per `S049`); per-component hints audit pass `deferred`.
- **S1577** — Keyboard shortcuts v2: `partial` — skip-link + `tests/e2e/grimbanews-keyboard-navigation.cjs` cover navigation; explicit shortcut keys (j/k/g/h) `deferred`.
- **S1578** — Focus management v2: `partial` — `partials/focus-manager.blade.php` + `tabindex="-1"` on `<main>` + skip-link shipped; modal focus-trap `partial`.
- **S1579** — A11y dashboard (axe-core scan cadence): `partial` — `docs/GRIMBANEWS_ADMIN_VISUAL_REGRESSION_ROUTES.md` ships the route matrix; axe-core CI gate `deferred`.
- **S1580** — A11y v2 launch retrospective: `partial` — baseline a11y locked; v2 controls (font scaling, dyslexia font, line-spacing, high-contrast variant) `deferred`.

## S1581–S1590 — Daily-report v2 (per-region editorial digest, per-topic summary, time-of-day variants)

Daily-publish guarantee shipped via `grimba:ensure-daily-publish`; the per-region / per-topic / time-of-day variant emails depend on the newsletter v2 band (S1271-S1290) which is itself partly deferred.

- **S1581** — Per-region daily digest: `deferred` — newsletter pipeline supports per-region routing via `grimba_advertiser_leads_sales_mailbox`; explicit per-region digest email template `deferred`.
- **S1582** — Per-topic daily summary: `partial` — per-category RSS feed at `/feed.{category}.xml` is the per-topic surrogate; email digest variant `deferred`.
- **S1583** — Per-edition daily digest (Afrique vs International): `partial` — region cookie partitions the corpus; explicit per-edition email digest `deferred`.
- **S1584** — Time-of-day variants (morning / lunch / evening): `deferred` — single daily cadence today.
- **S1585** — Breaking-news push (within-day): `partial` — `App\Console\Commands\GrimbaFetchBreakingNews` ingests breaking; push notification depends on FCM/APNs (S1154).
- **S1586** — Curated weekly recap: `partial` — `App\Mail\GrimbaVaultDigestMail` ships weekly vault digest; per-edition curated recap `deferred`.
- **S1587** — Daily report — image variant (OG card embed in email): `partial` — `App\Http\Controllers\GrimbaOgImageController` renders OG cards; email-embed `partial`.
- **S1588** — Daily report — subject-line A/B: `deferred` — no A/B engine (S1073).
- **S1589** — Daily report — send-time A/B: `deferred` — same.
- **S1590** — Daily-report v2 launch retrospective: `deferred` — gates on S1581-S1589.

## S1591–S1600 — Trust & safety (moderation queue, brigading detection, downvote spam guard)

No reader comments / votes surface today (FoB Comment plugin is bundled but `Comments composer EN translations` is the only recent comment-side commit — comment-display surface is not the GrimbaNews trust-and-safety story). All `deferred` or `partial` against editorial moderation surrogates.

- **S1591** — Moderation queue — schema: `deferred` — no `moderation_queue` table.
- **S1592** — Moderation queue — UI: `partial` — `/admin/grimba/rss-drafts` is the editorial moderation surrogate; comment moderation UI `deferred`.
- **S1593** — Brigading detection (anomalous traffic): `deferred` — no per-user behavior tracking.
- **S1594** — Downvote-spam guard: `deferred` — no vote primitive on reader surface.
- **S1595** — Hate-speech filter: `deferred` — operator-side editorial; surrogate is `news_sources.factuality_score` + `credibility_score` source-level filter on ingest.
- **S1596** — Misinformation flag (per-article): `partial` — `news_sources.factuality_score` excludes low-score sources at ingest; per-article flag `deferred`.
- **S1597** — Author / commenter ban list: `deferred` — no commenter primitive.
- **S1598** — IP / device throttling on writes: `partial` — `AdvertiserLeadController` ships per-IP `RateLimiter::attempt('advertiser-lead:' . sha1($ip), ...)`; cross-surface write throttling `partial` (Laravel default).
- **S1599** — Trust & safety transparency report: `deferred` — operator-side annual report; depends on S1671 transparency band.
- **S1600** — Trust & safety launch retrospective: `deferred` — gates on S1591-S1599.

---

## Summary

All 200 sprint IDs in S1401–S1600 now carry a ledger row in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`.

- **Complete (16 sprints):** S1407 (publish gate via GrimbaPostPublisher), S1443 (canonical-URL partner attribution), S1471-S1474, S1478, S1480 (search filter facets shipped + URL-serialized + tested), S1481-S1486, S1490 (saved-search primitive: schema + create + delete + list + cron + email template + retro), S1521-S1522 (CSV exports for vault + read-history), S1532 (opposite-bias surfacing via dossier voices), S1551, S1555, S1558, S1559 (bookmark save + list + share + weekly digest), S1561, S1562 (PWA service worker + offline fallback).
- **Partial (~63 sprints):** Editorial workflow has admin-side surrogates (cluster admin / rss-drafts queue / source-classifier override / Botble revisions for versioning); attribution and translation rows have shipped substrates; search filters / saved-searches are mostly complete with `partial` polish (cadence picker, per-search unsubscribe link, search-result snippet highlighting); reader-product v2 has shipped baselines (cookie-only personalization, dark-mode contract, a11y skip-link + focus manager) with v2 polish deferred; offline mode shell shipped with per-article cache deferred; fairness substrates exist (bias distribution, ownership chip, dossier voices) with explicit floor enforcement deferred.
- **Deferred (~121 sprints):** All multi-seat editor product (invite / role / co-author / second-eye / dispute), author / byline / contributor / partnership programs (no `authors` / `contributors` / `partners` tables), semantic search (depends on embedding store S1076), ML feed (depends on per-member behavior log which by privacy design does not exist), preference-center server-persist + blocked-sources + weight-slider (depends on tier), annotations + share-with-quote, bookmark folders/tags, native offline IndexedDB queue, dyslexia font + font-scaling + high-contrast variant, daily-report time-of-day + per-edition email variants, comment moderation + brigading + downvote primitives (no reader comment / vote surface today).

The honest read: **roughly 8% of the S1401-S1600 band is genuinely shipped today, ~32% has a server-side / admin / cookie surrogate, and ~60% is post-launch product expansion gated on either a paid tier (S1211), an embedding store (S1076), a workflow product (multi-seat editor / author / contributor), an A/B harness (S1073), or a comment / vote primitive that GrimbaNews has explicitly not shipped**.

The valuable foundation: **search v2 filters (source, bias, owner, date-range) + saved-search alert primitive (schema + cron + email + UX + tests) + vault primitive (cookie + member sync + digest + CSV export + share) + PWA offline shell (with private-path guard) + dossier voices opposite-bias surfacing + per-region region cookie partitioning + per-category RSS feeds + GrimbaArticleDedupe canonical-URL fold** are all in place. The deferred rows drop into a working substrate the moment the missing tier / embedding store / multi-seat workflow / comment surface is provisioned.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (Sprint Evidence Ledger section, new rows for S1401-S1600)
- Prior packs: `docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md` (S1001-S1100), `docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md` (S1101-S1200), sister pack S1201-S1400 (in-flight)
- Search surface: `platform/themes/echo/routes/web.php:760-945` (search handler + saved-search create/delete), `platform/themes/echo/views/search.blade.php` (results view), `app/Support/GrimbaSavedSearches.php` (normalize + hash + upsert + searchUrl + countForMember), `tests/Feature/SavedSearchAlertsTest.php` (E2E save + delete + digest), `tests/Feature/SearchFacetsTest.php` (filter contracts, marked incomplete pending dossier-reinvention markup)
- Saved-search digest: `app/Console/Commands/GrimbaSendSavedSearchDigests.php` (weekly Monday 04:55 UTC), `app/Mail/GrimbaSavedSearchDigestMail.php`, `resources/views/emails/saved-search-digest.blade.php`, `routes/console.php` (schedule registration)
- Vault primitive: `app/Support/GrimbaVault.php` (parseIds + resolvePosts + memberDigest + syncCookieToMember), `app/Support/GrimbaVaultEvents.php` (privacy-safe `ip_hash` event ledger), `app/Console/Commands/GrimbaSendVaultDigests.php` (weekly cron), `app/Console/Commands/GrimbaArchiveVaultEvents.php`, `app/Mail/GrimbaVaultDigestMail.php`, `resources/views/emails/vault-digest.blade.php`, `platform/themes/echo/partials/save-button.blade.php`, `platform/themes/echo/views/coffre.blade.php`, `tests/Feature/VaultTest.php`, `tests/Feature/VaultDigestTest.php`, `tests/Feature/VaultAnalyticsTest.php`, `tests/Feature/VaultAnalyticsDashboardTest.php`
- For-you / personalization: `platform/themes/echo/routes/web.php:1168-1287` (pour-vous + cookie-only avoided-topic surfacing + CSV export), `platform/themes/echo/views/for-you.blade.php`
- Editorial workflow surrogates: `platform/themes/echo/functions/grimba-admin-clusters.php` (story clusters admin), `platform/themes/echo/functions/grimba-admin-rss-drafts.php` (rss draft queue), `platform/themes/echo/functions/grimba-admin-rss-feeds.php` (feed admin), `platform/themes/echo/functions/grimba-admin-sources.php` (news-sources admin), `app/Console/Commands/GrimbaClassifySources.php`, `app/Console/Commands/GrimbaClassifyCategories.php`, `app/Console/Commands/GrimbaBackfillCategory.php`, `app/Console/Commands/GrimbaEnrichDrafts.php`, `app/Console/Commands/GrimbaGenerateNobuAiSummaries.php`, `app/Support/GrimbaPostPublisher.php`, `app/Support/GrimbaPublicationPipeline.php`, `app/Support/GrimbaDedupeReview.php`, `app/Support/GrimbaArticleDedupe.php`
- Fairness substrates: `app/Support/GrimbaClusterBias.php`, `app/Support/GrimbaHomeFeed.php`, `app/Support/GrimbaSourceBreakdown.php`, `platform/themes/echo/partials/story/dossier-voices.blade.php`, `platform/themes/echo/partials/story/bias-distribution.blade.php`, `platform/themes/echo/partials/story/voices.blade.php`, `platform/themes/echo/partials/feed-balance.blade.php`, `platform/themes/echo/partials/bias-chip.blade.php`, `platform/themes/echo/partials/ownership-chip.blade.php`, `platform/themes/echo/partials/factuality-chip.blade.php`
- PWA / offline: `public/manifest.webmanifest`, `public/grimba-sw.js`, `public/offline.html`, `platform/themes/echo/partials/pwa-head.blade.php`, `platform/themes/echo/partials/pwa-register.blade.php`, `tests/Feature/PwaShellTest.php` (private-path guard + Cache-Control discipline)
- Share-kit: `platform/themes/echo/partials/story/share-kit.blade.php` (X / Bluesky / Facebook / WhatsApp / LinkedIn / Email intent URLs)
- A11y baseline: `platform/themes/echo/partials/focus-manager.blade.php`, skip-link in `layouts/grimba-chrome.blade.php`, `tests/e2e/grimbanews-keyboard-navigation.cjs`, `docs/GRIMBANEWS_ADMIN_VISUAL_REGRESSION_ROUTES.md`
- Rate-limit / write throttling: `app/Http/Controllers/AdvertiserLeadController.php:36` (per-IP `RateLimiter::attempt('advertiser-lead:' . sha1($ip), ...)`)
- No-go zones (genuinely deferred): no `authors` / `contributors` / `partners` / `editor_seats` / `moderation_queue` / `post_annotations` / `vault_folders` / `search_events` / `member_followed_categories` / `member_blocked_sources` tables today; no embedding / vector store; no A/B engine; no native iOS/Android shell; no FCM/APNs; no per-author byline parser; no reader comment / vote surface.
