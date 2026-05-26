# GrimbaNews — Partner Developer Docs Plan

**Status:** plan v0 (no partner-facing doc site; Atom 1.0 / RSS 2.0 specs externally available is surrogate for current RSS surface)
**Owner:** Michael O'Connor (Technical Writer) owns + Rajesh Kumar reviews accuracy + Henry Walker on tone + Steve Jobs on design
**Walks:** Mythos S1186 (Partner docs) deferred → partial
**Gating dependency:** API v2 shipped + OpenAPI spec generated (S1250)

## Why this exists

S1186 is the developer-facing doc site at `https://docs.grimbanews.com`. Partners self-serve onboarding — no support ticket needed for basic integration questions.

## Today's surrogate

- **External RSS spec docs** — RSS 2.0 / Atom 1.0 are widely documented; GrimbaNews-specific feed URLs listed at `/methodology` page.
- **`/methodology`** — explains the bias rubric, ingestion approach.
- **No code-sample-driven API docs.**

## Architecture pick

| Option | Pros | Cons |
|---|---|---|
| Docusaurus (Vercel-style) | popular, Markdown-driven, OOTB search | extra hosting concern |
| Mintlify | modern, OpenAPI-first, free for OSS-tier docs | vendor lock |
| Hugo + DocSearch | static, full control | more dev effort |
| Embedded in main site (`/docs/*`) | no separate hosting | clutters main domain |

**Recommendation:** Mintlify for v1 (fast to launch), Docusaurus self-hosted at v2 (cost + lock control).

## IA — sections

1. **Overview**
   - What is the API
   - License (CC-BY-NC-4.0 academic, commercial via contract)
   - Quickstart (5-min curl example)
2. **Authentication**
   - API key issuance flow
   - Bearer token format
   - Rate limits per tier
3. **Endpoints**
   - Posts
   - Clusters
   - Sources
   - Search
   - Trends
   - Bias distribution
4. **Pagination + filtering**
5. **Errors**
6. **Webhooks** (when shipped — gates on S1238)
7. **SDKs** (when shipped — gates on S1240)
8. **Code samples** — PHP, Python, JS, Go, R (academic-friendly)
9. **Changelog**
10. **Methodology references** — link to public methodology page

## Voice (Henry Walker / Michael O'Connor)

- Direct, no jargon.
- Code samples copy-pasteable.
- Per-endpoint: example request + example response side-by-side.
- "Common gotchas" callouts.
- Per-locale doc copies: FR + EN at launch.

## Update cadence

- Auto-generated reference from OpenAPI spec (per S1250) on each release.
- Hand-written sections (overview, quickstart, gotchas) reviewed quarterly.

## Search

- DocSearch / Mintlify built-in.
- Indexed: all section pages.
- Per-endpoint query lands on that endpoint.

## SEO

- `docs.grimbanews.com` indexed by Google.
- Sitemap.xml generated.
- Each endpoint = one page = one URL.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1186)
- Sister docs: `docs/GRIMBANEWS_PUBLIC_API_V2_DESIGN.md`, `docs/GRIMBANEWS_API_V2_OPENAPI_SPEC_SCOPE.md`, `docs/GRIMBANEWS_PARTNER_OPS_PLAYBOOK.md`, `docs/GRIMBANEWS_PARTNER_SANDBOX_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
