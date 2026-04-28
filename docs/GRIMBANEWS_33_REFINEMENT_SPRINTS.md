# GrimbaNews 33 Refinement Sprints

Date: 2026-04-28
Scope: Frontend + backend refinements after the core GroundNews-style product pass.

## Sprint List

1. [Shipped] Breakdown elegance: compact desktop/mobile comparison UI, source chips, motion safety, and backend tests for containment.
2. [Shipped] Breakdown data depth: normalize source factuality, ownership labels, and bias confidence so the UI is not just decorative.
3. [Shipped] Story page hierarchy: refine article/comparison page spacing, NobuAI insight placement, subscriber gate, and full-article readability.
4. [Shipped] Homepage hero readability: final contrast pass across all hero/card variants, with visual regression assertions.
5. [Shipped] Language-priority feed polish: validate native-language-first ordering on home, search, category, source, and comparison pages.
6. [Shipped] Translation cache hardening: make NobuTranslation failures visible in admin, retryable, and safe for FR to EN plus EN to FR.
7. [Shipped] Daily automation reliability: make RSS, NewsAPI, publish, full-text, category, translation, and NobuAI schedule health measurable.
8. [Shipped] RSS scale-up: add source health scoring, timeout isolation, last-success timestamps, and stale-feed admin warnings.
9. [Shipped] NewsAPI scale-up: category/country run ledger, per-country limits, dedupe metrics, and cost/usage guardrails.
10. Source logo quality: cache logo misses, improve source logo fallbacks, and expose logo status in admin sources.
11. Source credibility admin UX: improve source triage forms, sorting, bias confidence, factuality, and ownership workflows.
12. Coverage map polish: make admin and public coverage maps faster, readable in light/dark mode, and actionable.
13. Admin cockpit speed: reduce dashboard query cost, add run status cards, and keep dropdowns solid/readable.
14. [Shipped] Admin dark/light fidelity: audit every `/admin/grimba/*` custom screen for theme parity and hover states.
15. Command palette: finish public command palette UI, keyboard flow, and non-blog URL semantics.
16. Ads foundation: place non-intrusive ad slots, admin documentation, consent gating, and premium/subscriber suppression.
17. Ad yield readiness: add provider slot taxonomy for GAM/AdSense/Prebid/Amazon without hardcoding unsafe vendors.
18. Subscriber experience: full-article reader polish, saved articles, read history, and subscriber-only clear value.
19. Newsletter product: bias-mix digest, blindspot digest, source-follow digest, and unsubscribe/audit flows.
20. Blindspots polish: improve one-sided story detection, public explanation copy, and edge-case empty states.
21. Clustering quality: improve orphan matching, cluster split/merge admin tooling, and stale insight invalidation.
22. Dedupe hardening: catch copied wire stories, duplicate RSS entries, slug collisions, and image duplicates.
23. Image pipeline: improve og:image extraction, placeholder quality, cache strategy, and publisher hotlink safety.
24. Local/Canada edition: source coverage, native-language ranking, topic mix, and empty-state protection for Canada.
25. Search UX: fast facets, source/owner/factuality filters, translated/native indicators, and no legacy blog language.
26. Accessibility pass: keyboard navigation, focus rings, contrast, reduced motion, and screen-reader labels.
27. PWA polish: offline shell, service-worker cache discipline, install prompt, and mobile nav reliability.
28. Performance pass: query count, cache keys, public page TTFB, image sizes, and admin page responsiveness.
29. Observability: structured logs, health commands, scheduler heartbeat, and admin-visible failure summaries.
30. Security/privacy: cookie consent enforcement, API key redaction, image proxy constraints, and admin-only diagnostics.
31. Test suite expansion: feature tests for scheduler, command palette, comparison UI, translation ordering, and ads slots.
32. Content taxonomy: categories, review buckets, source country/language mappings, and public labels cleanup.
33. Production readiness closeout: deployment checklist, rollback notes, cron instructions, seed/idempotency checks, and final smoke.

## Execution Rule

Each sprint ships with:
- Frontend: visible reader/admin UI refinement or regression protection.
- Backend: data, automation, route, command, or persistence improvement.
- Verification: focused test plus full test suite when routes, layout, automation, or persistence are touched.
- Git: commit and push after the sprint passes.
