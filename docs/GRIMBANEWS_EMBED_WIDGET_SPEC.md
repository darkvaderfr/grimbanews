# GrimbaNews — Embed Widget Spec

**Status:** spec v0 (no embed route shipped)
**Owner:** Steve Jobs (CPO) on UX + Jacob Lee (DevOps) on isolation + Sara Chen (CISO) on token scope
**Walks:** Mythos S1651 (embed widget — /embed/{cluster-id} route) + S1652 (embed iframe-friendly layout) deferred → partial
**Gating dependency:** Embed-token API (S1654) + iframe-friendly stripped chrome layout. Spec itself is operator-side.

## Why this exists

S1651 + S1652 share a root: no embed surface exists today. The plan flagged "embed widget — `/embed/{cluster-id}` route — deferred." But the **spec** for what an embed should look like, what it should expose, and what it should require — that's operator-side work that gates the engineering. This document defines the spec so once Vader + Steve sign off it's a straight ship sequence.

## Use cases

1. **External publisher embeds a GrimbaNews cluster** — e.g., a regional blog wants to show "All sides of [cluster X]" on their article page.
2. **Press release embeds a bias-bar chart** — e.g., a media-literacy NGO embeds the cluster's bias distribution chart inline.
3. **Educator embeds a per-city local feed** — e.g., teacher embeds Africa francophone education news for class context.
4. **Newsletter embeds** — e.g., partner newsroom embeds a digest stream.

## Embed shapes

| Shape | Route | What it shows |
|---|---|---|
| Cluster | `/embed/cluster/{id}` | Cluster headline + 3-5 representative articles + bias bar |
| Bias chart only | `/embed/bias-chart/{cluster-id}` | Standalone bias-distribution chart (S1661 sister) |
| Per-region feed | `/embed/region/{region-code}` | Top 5 regional clusters, list view |
| Per-category feed | `/embed/category/{category-slug}` | Top 5 category clusters |
| Per-city feed | `/embed/city/{country}/{city-slug}` | Per-city stream (gates on `docs/GRIMBANEWS_CITY_TAXONOMY_SCHEMA.md`) |
| Daily digest | `/embed/daily-digest/{region}` | Today's per-region digest as cards |

## Iframe-friendly chrome

`platform/themes/echo/layouts/embed-chrome.blade.php` (new) — minimal layout:

- No `grimba-chrome.blade.php` global nav.
- No global footer.
- No newsletter signup widget.
- No comments anchor.
- No share kit (deferred per Phase 2).
- **Includes:** GrimbaNews wordmark (top-left), "Powered by NobuAI" footer (when AI-derived) + Methodology link.
- **CSS:** scoped + `prefers-color-scheme` aware; light fallback if iframe parent forces light.
- **No JS** for v1 — pure server-render. Optional progressive enhancement in v2.

## JS snippet generator (S1653 dependency)

Single-line snippet:

```html
<iframe src="https://grimbanews.com/embed/cluster/1234?token=abc"
        width="100%" height="640" frameborder="0"
        sandbox="allow-same-origin allow-popups"
        loading="lazy"
        title="GrimbaNews cluster: Climate policy debate">
</iframe>
```

Operators get the snippet via `/embed/preview?cluster=1234` (admin-only preview tool).

## Embed-token (S1654)

**Why** — Prevent unauthorized embedding (especially for partner content per `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md` syndication agreement).

**Schema:**

```sql
CREATE TABLE embed_tokens (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  token CHAR(40) NOT NULL UNIQUE,            -- sha256-derived
  owner_member_id BIGINT NOT NULL,           -- FK members.id (admin or paid Newsroom tier)
  owner_domain VARCHAR(255) NOT NULL,        -- e.g., 'partner-blog.com'
  embed_type ENUM('cluster','bias_chart','region','category','city','digest') NOT NULL,
  scope JSON DEFAULT '{}',                   -- {cluster_ids: [...], regions: [...], etc.}
  rate_limit_per_hour INT DEFAULT 1000,
  expires_at TIMESTAMP NULL,
  is_active BOOLEAN DEFAULT TRUE,
  last_used_at TIMESTAMP NULL,
  use_count BIGINT DEFAULT 0,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX (token),
  INDEX (owner_member_id, is_active),
  INDEX (owner_domain)
);
```

**Generation:** admin or paid-Newsroom-tier user generates via `/admin/grimba/embed-tokens` (new).

**Validation:** every `/embed/*` request validates token + checks `owner_domain` against `Referer` / `Origin` header (strict CORS).

**Public tier (no token):** allowed for cluster + bias-chart embeds, rate-limited to 100/hour per IP-hash. Educational + low-volume use.

## CSS isolation (S1655)

- **No Shadow DOM** for v1 (overkill; iframe is already isolation).
- **Scoped CSS** with `embed-*` class prefix.
- **`prefers-color-scheme`** honored; iframe parent can override via `?theme=light` / `?theme=dark` query.
- **Min/max width** constraints set so embed doesn't break parent layout.

## Branding (S1658)

- Top-left: GrimbaNews wordmark + link back to source (per `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md` source policy).
- Bottom-right: "Powered by NobuAI" — locked by `GrimbaNobuAiBrandPurityTest`. Always says "NobuAI", never the underlying provider.
- Newsroom-tier paid embeds (per `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md` tier table) may remove "Powered by NobuAI" footer.

## Analytics (S1656, S1657)

- **Per-embed impressions** — counted via `App\Support\GrimbaEmbedAnalytics::recordImpression()` (new). Logs token, embed_type, scope hash to `embed_impressions` table.
- **Click-through tracking** — UTM-tagged outbound links: `?utm_source=embed&utm_medium=widget&utm_campaign={token-hash}`.
- **Per-owner dashboard** at `/admin/grimba/embed-tokens/{id}/analytics`.
- Privacy: no per-viewer tracking; only aggregate impression counts + IP-hash dedupe.

## Responsive sizing (S1659)

- Embed reads its own iframe width via `window.parent.innerWidth` (when sandbox allows) OR via fixed breakpoints.
- 3 breakpoints: < 480px (mobile), 480-768px (tablet), > 768px (desktop).
- Min-height: 320px (single article card); max-height: 800px (cluster + 5 articles).

## A11y (S1668 partial → ship)

- Semantic HTML (`<article>`, `<section>`, `<figure>`).
- Alt text on every image.
- Bias chart has `<table>` fallback already (per S1668 partial) — preserve in embed.
- Keyboard-navigable per `tests/e2e/grimbanews-keyboard-navigation.cjs` baseline.

## Bot-detection (S1669)

- Headless-browser detection via TLS + UA fingerprinting.
- Rate-limit per IP-hash per minute.
- Token-required embeds bypass bot check (assumed authorized owner).

## Security headers (CSP, frame-ancestors)

- Embed routes serve `Content-Security-Policy: frame-ancestors *;` (intentionally permissive for embedding).
- Per-token enforcement: optional `allowed_referers` field on `embed_tokens` (default `*`).
- HSTS preserved per existing `GrimbaSecurityHeaders` middleware.

## Engineering effort estimate

- `embed-chrome.blade.php` layout + per-shape templates (6 shapes): 6 sprints.
- `embed_tokens` + `embed_impressions` schemas + admin UI: 4 sprints.
- Token validation middleware: 2 sprints.
- Snippet generator preview tool: 1 sprint.
- CSS isolation + scoping pass: 1 sprint.
- Analytics dashboard: 2 sprints.
- A11y + bot-detection sweep: 2 sprints.
- Tests + per-shape verification: 2 sprints.
- **Full ship: ~20 sprints.**

## Launch sequencing (gates on S1660 launch playbook)

1. Phase 1: bias-chart embed only (lowest risk, highest signal).
2. Phase 2: cluster embed + region embed.
3. Phase 3: category + city + digest embeds.
4. Phase 4: Newsroom-tier paid embeds (gates on `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md` Stripe).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1651-S1660, S1661-S1670, S1668)
- Sister docs: `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`, `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`, `docs/GRIMBANEWS_CITY_TAXONOMY_SCHEMA.md`, `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md`
- Brand-purity lock: `GrimbaNobuAiBrandPurityTest`
- Existing chrome layout: `platform/themes/echo/layouts/grimba-chrome.blade.php`
- Security headers: `app/Http/Middleware/GrimbaSecurityHeaders.php`
- Source-breakdown helper: `app/Support/GrimbaSourceBreakdown.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
