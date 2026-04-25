# GroundNews → GrimbaNews Gap Audit

**Date:** 2026-04-25
**Author:** Kai (Opus 4.7)
**Trigger:** Vader's NewsAPI wire request — "make sure GrimbaNews is replicating GroundNews and their articles evaluator"

This audit documents every meaningful feature surface on Ground News (https://ground.news) and maps it to GrimbaNews state. Goal: ship a credible francophone alternative.

---

## ✅ Already shipped (parity or near-parity)

| GroundNews feature | GrimbaNews state | Sprint |
|---|---|---|
| Bias rating per source (L/C/R) | `news_sources.bias_rating` | seed |
| Source ownership type | `news_sources.ownership_type` | seed |
| Factuality / credibility score | `news_sources.credibility_score` (0-100) | seed |
| Per-source profile pages | `/sources/{slug}` with editorial card + actual coverage distribution | S111 |
| Story clusters (multi-source coverage of one event) | `story_clusters` table + `posts.story_cluster_id` | seed |
| Story cluster bias bar (L/C/R distribution per story) | `partials/home/coverage-bar.blade.php`, gated on ≥2 sides | seed |
| Side-by-side comparison view | `/comparatif/{id}` | seed |
| Blindspot detection ("only one side covers") | `posts.is_blindspot` + `/angles-morts` | seed |
| Reader bias-mix profile | `partials/bias-mix.blade.php` (compact + full variants) | S100 |
| Topic following | `grimba_follow` cookie + `/pour-vous` | seed/S100 |
| RSS aggregation | 13 feeds, 30-min cadence, dedup ledger | S71-S81 |
| Search with bias facets | SQLite FTS5 at `/search?q=…&source=…&bias=…` | S75 |
| Reader history export (privacy win) | `/pour-vous/export.csv` — cookie-only, no server copy | S104 |
| Translation across language barriers | 8-provider chain branded as NobuAI | S84 + S97-S98 |
| Editorial visual chrome | Fraunces serif + paper bg + bias color stripes everywhere | S5+ |

---

## 🟡 Partial — needs depth

### Story coverage volume
**GroundNews:** ingests from 50,000+ sources globally.
**GrimbaNews:** 13 RSS feeds + NewsAPI integration just shipped (S128) — adds ~50 known outlets via `/everything` and `/top-headlines`. **Order of magnitude gap on volume**, but the architecture now scales — adding more queries / countries to NewsAPI config widens it without code changes.
**Next:** S132 (orphan-to-orphan cluster formation) is the unlock — without it, scale doesn't translate to coverage clusters.

### News source classification
**GroundNews:** every source has bias + factuality + ownership.
**GrimbaNews:** 67 sources classified (20 RSS-seeded + 47 NewsAPI-seeded, S129). Auto-created sources from unknown NewsAPI publishers default to `bias=unknown` and need editor review.
**Next:** S133 — admin queue at `/admin/grimba/news-sources?bias=unknown` to triage unclassified sources weekly.

### Bias rating granularity
**GroundNews:** uses 5 bins (Far Left / Lean Left / Center / Lean Right / Far Right) + "Unknown".
**GrimbaNews:** 4 bins (left / center / right / unknown). Coarser.
**Next:** S134 — schema-extend `bias_rating` with a numeric `bias_score` (-2.0 to +2.0) for finer placement on rendered bars. Backwards-compatible: existing 4-bin column stays.

### Mobile experience
**GroundNews:** dedicated iOS/Android apps + mobile-tuned web.
**GrimbaNews:** responsive web only. No PWA install path beyond the basics shipped at S40-ish.
**Next:** S117 (mobile audit) — already planned. Then S135 — improved PWA manifest + offline reading shell.

---

## ❌ Missing — concrete next sprints

### S132 — Orphan-to-orphan cluster formation [BLOCKING]
**Why GroundNews works:** every breaking story has 5-50 articles attached automatically. **Why our cluster bars are sparse:** `GrimbaRssPoller::findLikelyCluster()` only matches against `posts WHERE story_cluster_id IS NOT NULL`. Two un-clustered articles on the same event NEVER cluster.

**Fix:** when a new article fails the existing-cluster match, scan recent un-clustered articles too. If at least one matches above threshold, create a new `story_clusters` row and attach all matching articles (the new one + the existing orphan).

This is THE core sprint to land before launch — without it, /comparatif looks empty even with 1000s of articles.

### S136 — Bias bar on every article preview
**GroundNews:** every story card carries a tiny L/C/R micro-bar showing "47 sources covered: 28% L, 41% C, 31% R".
**GrimbaNews:** the bar exists in `partials/home/coverage-bar.blade.php` but only renders inside `hero-grid` and on `/comparatif`. Article cards in `/blog`, `/sources/{slug}`, search results don't carry it.
**Fix:** include the compact bar in `partials/blog/post/partials/items/card.blade.php` (and grid/list). Must gate on `story_cluster_id` having ≥2 sides — same rule as before.

### S137 — "Most read on Left/Center/Right" rankings
**GroundNews:** homepage rail showing the most-shared story on each side.
**GrimbaNews:** missing entirely.
**Fix:** new `posts.view_count` column + a daily cron computing `top_by_bias_24h` materialised view + a homepage rail partial.

### S138 — Topic feeds with pre-built bias bar
**GroundNews:** `/category/politics` shows the bias mix of stories in that category.
**GrimbaNews:** `/blog/{cat}` shows stories but without the meta-bar showing how the category is being covered.
**Fix:** compute the L/C/R mix of the visible posts on category pages, render a header bar.

### S139 — Source ownership map
**GroundNews:** "Owned by The Fox Corporation" with a tooltip showing other holdings. Anti-monopoly transparency.
**GrimbaNews:** `news_sources.ownership_type` is just an enum (`corporate / public / state-owned / etc`). No parent company.
**Fix:** add `news_sources.owner_name` + render on the per-source page. Bonus: a `/proprietaires` page showing concentration ("17 outlets owned by 4 entities").

### S140 — Story timeline
**GroundNews:** for major events, shows a chronological timeline of key articles ("first reported / leftward turn / rightward escalation").
**GrimbaNews:** stories are static — no temporal arc.
**Fix:** when a `story_cluster_id` has ≥10 articles spanning ≥48h, render a timeline strip on the comparatif page.

### S141 — "Show me the other side" inline CTA
**GroundNews:** on every article, a button "See 14 other coverages" → opens the cluster view.
**GrimbaNews:** the cluster link exists but is buried.
**Fix:** elevate to a sticky chip on single-post views.

### S142 — Newsletter with bias signal
**GroundNews:** daily newsletter "5 stories you missed because of your reading bias".
**GrimbaNews:** newsletter system exists (S119) but blasts the same content to everyone.
**Fix:** when a subscriber has reading-history available (cookie merge on signup), route them to the bias-balanced variant of the daily digest.

### S143 — Vault / saved-stories
**GroundNews:** subscribers can bookmark for later.
**GrimbaNews:** no bookmarking. The `grimba_read` cookie tracks visited posts but not "save for later".
**Fix:** add a `grimba_vault` cookie with starred post IDs + a `/coffre` reading-list page.

---

## Strategic gaps (bigger than a sprint)

### Authentication / accounts
**GroundNews:** has accounts, paid tier, social login.
**GrimbaNews:** explicitly cookie-only (privacy win + simpler ops). S113 (optional email-based account that syncs cookies across devices) is the right next step — keep cookie-only as default.

### Live event pages
**GroundNews:** /live during breaking events with auto-refreshing source aggregation.
**GrimbaNews:** static pages, 30-min refresh cadence.

### Paid tier
**GroundNews:** $9.99/mo for full archive + premium features.
**GrimbaNews:** no monetisation yet — Vader hasn't shipped Stripe integration on GrimbaNews specifically.

---

## Recommended ship order (next 10 sprints)

1. **S132** Orphan cluster formation — unlocks GroundNews-grade clusters at scale
2. **S136** Bias bar on every article preview — visible win on every reader page
3. **S133** Admin triage queue for unknown-bias sources — closes the long tail
4. **S141** "Show me other coverages" sticky chip on single-post — surfaces the unique value
5. **S138** Category bias bars
6. **S137** Most-read-on-side rails
7. **S139** Source ownership map
8. **S134** Bias score (-2 to +2) for finer rendering
9. **S140** Story timeline
10. **S142** Bias-aware newsletter digests

S132 + S136 alone close ~70% of the perceived gap.
