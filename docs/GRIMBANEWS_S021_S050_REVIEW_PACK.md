# S021–S050 — Page Reviews + Admin Reviews + Visual Audits

**Generated:** 2026-05-19
**Method:** review of route handlers + view files + existing lock tests + dark/light contract + Playwright specs.

Bundled because most of these are evidence-pointer rows; each cites the canonical artifact that closes the review.

---

## S021–S030 — Public-page review block

| Sprint | Surface | Evidence | Status |
|---|---|---|---|
| S021 | Homepage `/` | `platform/themes/echo/layouts/grimba-home.blade.php` + `partials/home/*` (5 rails); covered by S-CAT 10/10 + S-LSAT-06 locale filter + `GrimbaHomeRailsTest`; JSON-LD locked via Wave KKKKK; OG/Twitter/canonical via Wave RRRRRR–WWWWWWW | complete |
| S022 | Story page `/blog/{slug}` + `/article/{slug}` | `platform/themes/echo/views/post.blade.php`; S543 canonical, S531 fallback, S532 sanitization, Wave TTTTT NewsArticle JSON-LD, Wave UUUUUU article:* OG meta; `tests/Feature/StoryBreakdownTest.php` | complete |
| S023 | Comparison `/comparatif/{id}` (aka `/dossiers`) | `views/comparison.blade.php` + `views/comparison-index.blade.php`; Wave LLLLL CollectionPage JSON-LD + Wave WWWWWW share-kit + Wave MMMMMM/NNNNNN related rail; Wave KKKKKKK 404-on-empty + Wave MMMMMMM numeric route constraint | complete |
| S024 | Source page `/sources` + `/sources/{slug}` | Wave OOOOO CollectionPage JSON-LD; tests cover source rendering | complete |
| S025 | Search `/search` | Wave YYYYY SearchResultsPage JSON-LD + Wave OOOOOOO XSS escape; noindex via `seo-meta-config` predicate; `tests/Feature/GrimbaLaunchReadinessTest.php` covers search | complete |
| S026 | Local `/local` | local handler in routes/web.php; noindex via `seo-meta-config` predicate (geo-personalized) | complete |
| S027 | Auth | Botble auth + member middleware on /account, /coffre | complete |
| S028 | Subscriber | member dashboard hijack (S168 admin-style sidebar replaced); subscriber gate WIP | partial |
| S029 | Newsletter | newsletter overlay + footer signup; cookie-consent compatibility checked | complete |
| S030 | PWA | manifest.webmanifest + `partials/pwa-head.blade.php` + `partials/pwa-register.blade.php`; PwaShellTest covers contract; theme-color deterministic cookie-only (NOT prefers-color-scheme) | complete |

## S031–S040 — Admin-page review block

| Sprint | Surface | Evidence | Status |
|---|---|---|---|
| S031 | Cockpit | `resources/views/grimba-admin/cockpit.blade.php`; automation board (S612), translation map link, NobuAI provider credits tile | complete |
| S032 | Provider vault | admin settings (provider keys, NobuAI brand purity in admin OK per CLAUDE.md); redaction tests in `tests/Unit/GrimbaProviderCreditsTest.php` | complete |
| S033 | RSS admin | `Tour de contrôle RSS` admin page; visible-title regression test in `AdminSettingsTest.php` | complete |
| S034 | NewsAPI admin | admin form + save handler; S113 NewsAPI config guard | complete |
| S035 | Source triage | admin source registry; source quarantine + tier UI (S101) | complete |
| S036 | Cluster admin | cluster list/edit/merge/split admin (S211-S214) | complete |
| S037 | Coverage map | S-LANG-13 per-source coverage admin | complete |
| S038 | Translation admin | S-LANG-10 `/admin/grimba/translation-map` | complete |
| S039 | Cookie admin | admin cookie config + consent banner | complete |
| S040 | Ads admin | ad slot config + S-ADS leads admin (`/admin/grimba/ads-leads`) | complete |

## S041–S050 — Visual audit block

| Sprint | Surface | Evidence | Status |
|---|---|---|---|
| S041 | Light theme audit | `GrimbaDarkModeContractTest::test_surface_renders_light_mode_attrs_by_default`; S-MODE light parity 11/11 | complete |
| S042 | Dark theme audit | `GrimbaDarkModeContractTest::test_surface_renders_dark_mode_attrs_when_cookie_is_dark` + hardcoded-white-bg sweep + duplicate-body-class test; Wave UUUU + Wave VVVV + WWWW + XXXX + ZZZZ + AAAAA + CCCCC | complete |
| S043 | Mobile audit | `tests/e2e/grimbanews-mobile-shell-contrast.cjs` + S-PILL-08 mobile pass | complete |
| S044 | Desktop audit | Playwright specs cover 1280w; share-kit + related-dossier rail tested | complete |
| S045 | Incognito audit | covered implicitly by stateless test suite; cookie-gated features fall back gracefully | partial |
| S046 | Safari audit | not formally tested — Webkit Playwright spec exists but not in CI; **OPEN** | partial |
| S047 | Chrome audit | Playwright spec runs Chromium by default; ✓ | complete |
| S048 | Firefox audit | not formally tested — **OPEN** | partial |
| S049 | Screen reader audit | aria-label sweeps on share-kit + info-pill; `tests/Feature/GrimbaInfoPillTest.php` covers ARIA disclosure-widget; full screen-reader pass deferred | partial |
| S050 | Keyboard audit | skip-link works; focus-manager partial wired; `tabindex="-1"` on `<main>`; ESC + outside-click on info-pill (S-PILL-04); full keyboard pass deferred | partial |

---

## Closes

- S021, S022, S023, S024, S025, S026, S027, S029, S030 (complete)
- S031, S032, S033, S034, S035, S036, S037, S038, S039, S040 (complete)
- S041, S042, S043, S044, S047 (complete)
- S028, S045, S046, S048, S049, S050 marked partial — gaps noted

**Bundled total: 24 complete + 6 partial = 30 sprints reviewed.**

## Top 3 launch-relevant gaps from this pack

1. **S046 Safari audit + S048 Firefox audit** — Playwright Chromium spec runs but Webkit + Gecko don't. Pre-launch must run cross-browser smoke at minimum.
2. **S049/S050 full a11y pass** — info-pill + share-kit cover their ARIA contract; the surrounding 27 reader routes don't have an explicit axe-core / NVDA pass.
3. **S028 subscriber gate** — wired conceptually; needs end-to-end test covering paying vs free user paths.
