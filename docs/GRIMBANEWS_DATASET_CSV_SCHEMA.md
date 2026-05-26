# GrimbaNews — Dataset CSV Schema

**Status:** schema v0 (partial CSV exports ship today; researcher-grade catalog deferred)
**Owner:** Larry Ellison (VP DBA) on schema + Lucy Leai (Strategy) on license + Sara Chen (CISO) on privacy review
**Walks:** Mythos S1681 (per-source CSV export), S1682 (per-cluster CSV export), S1683 (per-day CSV export) deferred → partial
**Gating dependency:** Dataset-license decision (operator-side, gates on `docs/GRIMBANEWS_OSS_METHODOLOGY_SCOPE.md` license vote) + per-endpoint route. Schema design itself is operator-side.

## Why this exists

S1681-S1683 share a root: GrimbaNews ships **point-in-time CSV exports** for vault events (S1685, S1686 complete) + read-history (S1684 complete) but no **researcher-grade per-source / per-cluster / per-day catalog**. The exports we have are reader-side; the researcher dataset needs different shape, license, and access controls. This document defines the schemas.

## Today's shipped exports

| Endpoint | Shape | Privacy posture | Owner |
|---|---|---|---|
| `/coffre/export.csv` | Per-reader vault picks | Member-owned data; no public access | `routes/web.php:1913+` |
| `/pour-vous/export.csv` | Per-reader read history | Same | `routes/web.php:1235-1287` |
| `storage/exports/vault_events_YYYY-MM.csv` | Monthly anon vault events | `ip_hash` only, no PII | `app/Console/Commands/GrimbaArchiveVaultEvents.php` |

**No researcher-grade dataset endpoint** exists.

## Proposed researcher exports

### 1. Per-source dataset (S1681 ship target)

**Endpoint:** `/datasets/sources.csv` (paid-research tier — gates on `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`).

**Columns:**

| Column | Type | Source | Notes |
|---|---|---|---|
| source_id | int | `news_sources.id` | |
| source_name | string | `news_sources.name` | |
| source_url | string | `news_sources.website` | |
| country_code | char(2) | `news_sources.country` | ISO 3166-1 |
| editorial_category | string | `news_sources.editorial_category` | |
| bias_rating | string | `news_sources.bias_rating` | left / center-left / center / center-right / right / unclear |
| factuality_score | int | `news_sources.factuality_score` | 0-100 |
| credibility_score | int | `news_sources.credibility_score` | 0-100 |
| ownership_type | string | `news_sources.ownership_type` | public / private / state / cooperative |
| owner_name | string | `news_sources.owner_name` | |
| primary_language | char(2) | `news_sources.primary_language` | |
| is_active | bool | `news_sources.active` | |
| first_seen_at | datetime | `news_sources.created_at` | |
| classification_methodology_version | string | per `docs/GRIMBANEWS_SOURCE_CLASSIFICATION_METHODOLOGY.md` git SHA | |

**Excluded** (privacy + license-sensitive): per-source API keys, license_notes (contract-confidential).

### 2. Per-cluster dataset (S1682 ship target)

**Endpoint:** `/datasets/clusters.csv?from=YYYY-MM-DD&to=YYYY-MM-DD` (paid + date-window required).

**Columns:**

| Column | Type | Source | Notes |
|---|---|---|---|
| cluster_id | int | `story_clusters.id` | |
| cluster_title | string | `story_clusters.title` | First-published title or operator override |
| earliest_post_at | datetime | min(`posts.created_at` where story_cluster_id) | |
| latest_post_at | datetime | max(`posts.created_at` where story_cluster_id) | |
| post_count | int | count(`posts.id` where story_cluster_id) | |
| source_count | int | count(distinct `posts.news_source_id`) | |
| bias_left_count | int | `App\Support\GrimbaSourceBreakdown.resolve($cluster).left` | |
| bias_center_count | int | same .center | |
| bias_right_count | int | same .right | |
| bias_unknown_count | int | same .unknown | |
| middle_ground_flag | bool | cluster is Middle Ground per Wave CCCCCCCCCCC-KKKKKKKKKKK | |
| editorial_region | string | `posts.editorial_region` mode | |
| editorial_category | string | `posts.editorial_category` mode | |
| canonical_url | string | `/dossier/{id}` | |

**Excluded:** member-side engagement counts (vault saves, shares) per privacy posture.

### 3. Per-day dataset (S1683 ship target)

**Endpoint:** `/datasets/daily.csv?from=YYYY-MM-DD&to=YYYY-MM-DD` (paid).

**Columns:**

| Column | Type | Source | Notes |
|---|---|---|---|
| date | date | grouping | |
| total_posts | int | count posts published that day | |
| total_clusters_created | int | count story_clusters created that day | |
| total_clusters_active | int | count clusters with ≥1 post that day | |
| total_sources_active | int | count distinct news_source_id with a post that day | |
| per_region_posts | json | {africa: N, international: N, dom-tom: N} | |
| per_category_posts | json | {politics: N, climate: N, ...} | |
| per_bias_posts | json | {left: N, center: N, right: N, unknown: N} | |
| middle_ground_clusters | int | count clusters tagged Middle Ground | |

### 4. Vault events (already ships per S1686)

Existing schema unchanged: `event, post_id, ts, ip_hash` (4-column, monthly file).

## License

Gates on `docs/GRIMBANEWS_OSS_METHODOLOGY_SCOPE.md` license decision. Recommended default: **CC BY-NC 4.0** for non-commercial researchers; commercial license bilateral.

**Citation requirement** (S1688, S1697 deferred): `Use of this dataset requires citation: "GrimbaNews News Bias Dataset, GrimbaNews / Iboga Ventures, {version}, accessed {date}, https://grimbanews.com/datasets"`.

## Versioning (S1689)

- **Snapshot-style:** filename includes version + date. e.g., `sources_v1_2026-05-26.csv`.
- **`/datasets/index.csv`** lists all available versions.
- **Old versions retained** for citation reproducibility — 5-year retention minimum.
- Schema changes bump version (v1 → v2); each version has a schema doc.

## Privacy posture

- **No reader PII** in any researcher dataset.
- **IP-hash only** (never raw IP) — per existing vault_events pattern.
- **Per-member exports** (vault, read-history) accessible only to the member themselves; do NOT enter researcher datasets.
- **Operator data** (admin actions, moderator decisions) excluded.

## Access controls

- **Public tier (no auth):** /datasets/clusters.csv with ≤ 1-day window; rate-limited per IP-hash.
- **Researcher tier (auth + verification per S1693):** larger windows, per-source dataset.
- **Commercial tier (paid):** full historical + commercial license.

## Page

`/datasets` landing page — explains license, citation, contact, methodology link.

Required sections:
1. License terms (CC BY-NC 4.0 default).
2. Citation format.
3. Available datasets + sample.
4. Methodology link to `/methodologie`.
5. Contact for commercial inquiries.
6. Schema documentation links.

## Engineering effort estimate

- Per-source CSV endpoint: 1 sprint.
- Per-cluster CSV endpoint: 2 sprints (with date-window filtering).
- Per-day CSV endpoint: 2 sprints.
- `/datasets` landing page: 1 sprint.
- License + citation + version index: 1 sprint.
- Access-tier gating: 2 sprints (gates on member auth + tier per Stripe).
- Tests + sample-fixture verification: 1 sprint.
- **Full ship: ~10 sprints once license decision lands.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1681-S1690, sister S1697)
- Sister docs: `docs/GRIMBANEWS_OSS_METHODOLOGY_SCOPE.md`, `docs/GRIMBANEWS_SOURCE_CLASSIFICATION_METHODOLOGY.md`, `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`
- Existing exports: `routes/web.php:1235-1287` (read-history), `routes/web.php:1913+` (vault), `app/Console/Commands/GrimbaArchiveVaultEvents.php`
- Bias breakdown: `app/Support/GrimbaSourceBreakdown.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
