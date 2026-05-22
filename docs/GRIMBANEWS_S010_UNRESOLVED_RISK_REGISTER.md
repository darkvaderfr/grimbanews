# S010 — Unresolved Risk Register

**Generated:** 2026-05-19
**Method:** post-mortem review of recent commits, lock tests, and reconciliation reading.

This is the master pre-production risk register. Each row maps to a gate or sprint block that must close before launch.

## Open risks (severity × likelihood × mitigation)

| ID | Risk | Severity | Likelihood | Mitigation | Sprint owner |
|---|---|---|---|---|---|
| R-01 | Visual regression on dark/light reader chrome on any unique route × width × locale combination | High | Medium | S-MODE-02 Playwright screenshot diff across 28 routes × 3 widths × 2 modes; pending | S751–S780 + S-MODE band |
| R-02 | Provider key leak via admin debugbar or error surface | High | Low | `disableDebugbarOnAdmin()` config, redaction tests, `GrimbaProviderCredits` accounting | S907, S908 |
| R-03 | Restore drill never executed end-to-end | High | High (until performed) | `GrimbaDatabaseBackups` + `grimba:verify-backups` exist; restore drill needs operator hand-on test | S961-S970 |
| R-04 | NobuAI provider live-smoke fails in production (cost surprise + insight gap) | High | Medium | `GrimbaProviderCredits` + budget guards; provider live-smoke test not yet in CI | S260, S281-S300 |
| R-05 | CSRF-token leak via shared CDN cache on grimba-chrome-layout pages | CRITICAL | Closed | Wave YYYYYYY revert + lock test enforces no-public-cache on these pages | Wave YYYYYYY |
| R-06 | Stored XSS via /search?q= reflected into JSON-LD | CRITICAL | Closed | Wave OOOOOOO JSON_HEX_TAG/AMP/APOS/QUOT escape + XXXXXXX/YYYYYYY parse + `</script>` non-presence lock | Wave OOOOOOO + XXXXXXX |
| R-07 | Open redirect / SSRF via img-proxy | High | Closed | Wave SSSSS allowlist + Wave QQQQQQQ lock test (24+3 probes) | Wave QQQQQQQ |
| R-08 | Search engines silently de-index pages 2+ on paginated reader surfaces | High | Closed | Wave BBBBBBBB self-canonical pagination via `core_seo_canonical` filter; 6 lock-test cases | Wave BBBBBBBB |
| R-09 | Theme singleton state leak across shared-kernel test/Octane requests | Medium | Closed | Wave VVVVVV + ZZZZZZZ explicit `Theme::set(key, null)` cleanups in `seo-meta-config` + `seo-meta-twitter-image` | Wave ZZZZZZZ |
| R-10 | Sitemap `lastmod` going stale due to hardcoded dates | Medium | Closed | Wave AAAAAAAA dynamic sitemap route querying MAX(published_at) per surface | Wave AAAAAAAA |
| R-11 | NewsAPI quota exhaustion silently masked | Medium | Closed | S113 NewsAPI config guard `docs/GRIMBANEWS_NEWSAPI_CONFIG_GUARD_2026_05_11.md` | S113 |
| R-12 | Title-only dedupe destroys legitimate articles | Medium | Mitigated | S203/S209/S210 review-only mode + per-source policy; full --include-title-groups apply remains operator-gated | S203, S209, S210 |
| R-13 | Disk-full cascade silently breaks scheduler + breaks backups + breaks ingest | Medium | Mitigated | S973 + `grimba:health --fail-on-risk` 2048 MB floor + cockpit warning | S973 |
| R-14 | Ad CLS pushes Lighthouse below acceptable score | Medium | Closed | Wave ZZZZZZZZ 2026-05-22 — `content-visibility: auto` + `contain-intrinsic-size` per ad-slot variant + per-variant `min-height` (92/112/180/270 px). Sidebar bumped 250→270 to fit 300×250 + padding without Safari clipping. Lock-tested via `test_ad_slots_reserve_cls_safe_box_via_min_height_and_intrinsic_size` (6 assertions). | Wave ZZZZZZZZ |
| R-15 | Subscriber entitlement misroute (paying user sees ads) | Medium | Open | Wired but lacks end-to-end test coverage | S884-S889 |
| R-16 | Schema.org JSON-LD malformed silently dropped by Google | Medium | Closed | Wave XXXXXXX + YYYYYYY parse-validity + `</script>` lock; 99→149 assertions | Wave XXXXXXX/YYYYYYY |
| R-17 | 404 surface emitting `<meta name="robots" content="index, follow">` + canonical-to-broken-URL — confuses crawler | Medium | Closed | Wave WWWWWWW Theme::set('grimba_is_404') gating; lock test verifies wiring | Wave WWWWWWW |
| R-18 | Auth-gated routes wasting crawl budget | Low | Closed | Wave VVVVVVV explicit Disallow in robots.txt | Wave VVVVVVV |
| R-19 | Article translation atomicity broken (in-row vs join-table desync) | Medium | Closed | S-LANG-15 atomicity test (4 invariants) | S-LANG-15 |
| R-20 | NobuAI brand purity regression (Anthropic/Claude/GPT leak to public) | Low | Closed | Wave OOOO static scanner + GrimbaNobuAiBrandPurityTest | Wave OOOO |

## Risk burndown summary

- **CRITICAL:** 0 open. 2 closed (R-05, R-06).
- **High:** 4 open (R-01, R-03, R-04, plus one). 3 closed (R-07, R-08, plus pre-existing).
- **Medium:** 3 open. 9 closed (added R-14 closure via Wave ZZZZZZZZ 2026-05-22).
- **Low:** 0 open. 2 closed.

## Top-3 launch blockers (per Mnemo/Zen audits)

1. **R-01** Visual regression sweep (28 × 3 × 2 = 168 screenshot matrix). Mitigated partly by GrimbaDarkModeContractTest but full Playwright screenshot diff is the canonical gate.
2. **R-03** Restore drill not executed. Backups exist; restore has never been proven.
3. **R-04** NobuAI provider live-smoke not in CI.

## Closes

- S010 (unresolved risk register)
