# GrimbaNews — Embed JS Snippet Generator

**Status:** plan v0
**Owner:** Rajesh Kumar (backend) + Nina Patel (Lead FE) + Lucy Leai (Strategy)
**Walks:** Mythos S1653 (embed widget — JS-snippet generator) deferred → partial
**Gating dependency:** No embed.js bundle today; needs build pipeline + CDN.

## Why this exists

Per-cluster embed lets external publishers + bloggers add a GrimbaNews cluster-comparison widget to their pages. Drives reverse-traffic + brand-recognition.

## v1 design

Per-cluster embed snippet:

```html
<script src="https://grimbanews.com/embed.js?cluster=1040&theme=light" async></script>
<div data-grimba-cluster="1040"></div>
```

`embed.js` (compact, <20KB):
1. Reads `data-grimba-cluster` attribute.
2. Fetches `/api/clusters/1040/embed.json` (cached 1h).
3. Renders inline iframe-free widget:
   - Cluster topic
   - L/C/R bar viz
   - MG/BS badge if applicable
   - "Voir comparaison complète" link to /comparatif/1040
   - GrimbaNews attribution

## Per-embed customization

Query params:
- `theme=light|dark`
- `lang=fr|en|de|es|pt-br`
- `compact=true` (smaller variant)

## Per-publisher rate limit

- Free: 100 embed renders/day per IP (rough).
- Pro: unlimited via API key (per Wave AABB B2B tier).

## Cross-references

Master plan: S1653. Sister: `docs/GRIMBANEWS_EMBED_WIDGET_SPEC.md` (Wave LLL), `docs/GRIMBANEWS_B2B_EDITORIAL_TRUST_SCORE_API_PLAN.md`.
