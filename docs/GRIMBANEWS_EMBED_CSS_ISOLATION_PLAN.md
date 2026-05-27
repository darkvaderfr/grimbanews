# GrimbaNews — Embed Widget CSS Isolation

**Status:** plan v0
**Owner:** Nina Patel (Lead FE) + Rajesh Kumar (backend)
**Walks:** Mythos S1655 (embed widget CSS isolation) deferred → partial
**Gating dependency:** Wave SUB-21 embed.js bundle live.

## Why this exists

Embed widget injected on third-party pages would inherit their CSS — broken layout. CSS isolation via Shadow DOM (modern browsers) or scoped CSS prefix (legacy fallback) keeps the widget rendering consistently.

## v1 approach: Shadow DOM

Use `attachShadow({ mode: 'closed' })` on the host element. Render via safe DOM construction (`document.createElement` + `textContent`), NOT direct HTML-string injection. DOMPurify can be loaded if sanitization is needed for user-supplied content (none in v1 — only our own server-fetched JSON).

- Shadow DOM closed mode prevents external script tampering.
- Per-embed CSS scoped to shadow root via `<style>` inside shadow.
- Browser support: 96%+ modern.

## v2 fallback: scoped CSS prefix

For browsers without Shadow DOM:
- All CSS prefixed with `.grimba-embed-{hash}`.
- Hash unique per embed instance.
- Fallback only ~4% traffic.

## Per-embed accessibility

- Shadow DOM does NOT block accessibility tree.
- ARIA labels propagated.
- Per-embed keyboard nav supported.

## XSS guardrails

- All embed content fetched from `/api/clusters/{id}/embed.json` (our trusted source).
- DOM construction via safe API (`createElement` + `textContent`); no `innerHTML` with untrusted strings.
- If future v2 allows user-defined templates: DOMPurify-sanitize per render.

## Cross-references

Master plan: S1655. Sister: `docs/GRIMBANEWS_EMBED_JS_SNIPPET_GENERATOR.md`.
