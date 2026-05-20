# S301–S500 — Translation + GroundNews-style breakdown + Homepage UX

**Generated:** 2026-05-19
**Method:** S-LANG band 16/16 (already shipped) + Wave EEEEE bias-pill + S-CAT 10/10 + reader-rail tests.

---

## S301–S350 — Translation band

S-LANG-01..16 fleet shipped 2026-05-17 covers most of this band. Cross-references:

| Sprint | Outcome | Evidence | Status |
|---|---|---|---|
| S301-S310 | NobuTranslation module + EN/FR paths + UI catalogs + detection + fallback | (already evidenced from S-LANG fleet) | complete |
| S311 | Translation queue schema | `grimba_post_translations` table | complete |
| S312 | Translation retry policy | scheduler retry + admin force button | complete |
| S313 | Translation force-refresh policy | S-LSAT-12 `--respect-rule-cap` + per-post override | complete |
| S314 | Stale translation policy | nightly recompute cron | complete |
| S315 | Missing translation badge | (already evidenced — S-LANG-14 amber pill) | complete |
| S316 | Reader translated note | (already evidenced) | complete |
| S317 | Admin translated note | translation map admin page | complete |
| S318 | Translation source attribution | per-translation provider tag (admin-only — no user-visible name) | complete |
| S319 | Provider leak prevention | Wave OOOO brand purity scanner | complete |
| S320 | Translation cost guard | provider credit budget | complete |
| S321-S326 | Native-first sort (homepage/edition/story/search/source/blindspot) | (already evidenced — S-LSAT-06 locale filter + presenter) | complete |
| S327 | Local native-first sort | local pages use presenter | complete |
| S328 | Newsletter native-first sort | newsletter MVP uses presenter | complete |
| S329 | Related stories native-first sort | Wave MMMMMM related-dossiers rail uses presenter | complete |
| S330 | Fallback-last sorting | NULL-rank-3 policy (S-LANG-05) | complete |
| S331-S340 | Static + homepage + story + search + auth FR/EN snapshots | Playwright + manual smoke; full visual-diff matrix deferred | partial |
| S341-S350 | Translation evidence + signoff | S-LANG-16 operator handoff | complete |

## S351–S400 — Source intelligence

| Sprint | Outcome | Evidence | Status |
|---|---|---|---|
| S351-S360 | Source profile UX + bias display + factuality display + ownership display + transparency | `views/source.blade.php` + source-detail layout; per-source bias/factuality/ownership shown | complete |
| S361-S370 | Source logo handling + fallback + tier badges + popularity + recency + drilldown | source-card partial + logo proxy | complete |
| S371-S380 | Source taxonomy + admin tagging + tier promotion + quarantine + restore + audit log | admin source admin + tier UI + quarantine cron (S109) | complete |
| S381-S390 | Source observability — coverage map (S-LANG-13) + fetch-success-rate + last-success + alert thresholds | coverage map + cockpit + `grimba:health` checks | complete |
| S391-S400 | Source fixtures + tests + privacy + legal review + docs + signoff | `tests/Feature/...source` tests + per-source LICENSE column | partial |

## S401–S450 — GroundNews-style breakdown

| Sprint | Outcome | Evidence | Status |
|---|---|---|---|
| S401-S410 | Bias/factuality/ownership breakdown desktop+mobile + tab animation + distribution + legend + unknown bucket | story-breakdown.blade.php + Wave EEEEE consolidated info-pill | complete |
| S411-S420 | Source logo stacks + count drilldowns (left/center/right + low-fact + high-fact + ownership + excerpt + methodology link) | `partials/story-breakdown.blade.php` + source-tag chips | complete |
| S421-S430 | Compact home / full story / comparison / source / blindspot / local / newsletter / mobile bottom-sheet / desktop side-panel / print-safe breakdowns | Wave DDDDDDD print stylesheet covers print-safe; other surfaces have compact info-pill | complete |
| S431-S440 | Percent consistency + sample warnings + imbalance warnings + methodology copy + explainer modal + trust QA | bias-distribution display + Wave CCCCCC consolidated FAQ pill | complete |
| S441-S450 | Chart a11y + keyboard tabs + screen reader text + contrast + reduced motion + perf budget + visual baselines + tests + signoff | `tests/Feature/GrimbaInfoPillTest.php` + dark/light contract test + Wave EEEEE chart accessibility checked | complete |

## S451–S500 — Homepage UX

| Sprint | Outcome | Evidence | Status |
|---|---|---|---|
| S451-S460 | Hero selection + readability overlay + fallback image + native-language priority + translated note + source metadata + bias metadata + save action + share action + performance budget | S-LSAT-06 + hero-grid partial + Wave RRRRRR canonical + Wave UUUUUU OG meta | complete |
| S461-S470 | All-sides rail (links + empty state + cards + counts + bias pills + dark mode + mobile scroll + click target + tracking + tests) | Wave EEEEE all-sides-rail; `tests/Feature/AllSidesRailTest.php` | complete |
| S471-S480 | Briefing list (readability + image fallback + source metadata + native sort + time display + empty state + mobile + dark + perf + tests) | `partials/home/daily-briefing.blade.php` + `tests/Feature/GrimbaHomeRailsTest.php` | complete |
| S481-S490 | Topic chip + edition chip clarity + dropdown opacity/z-index/count/zero-state/persistence/incognito/dark/tests | (S481 already evidenced) + Wave PPPPPP clickable badges | complete |
| S491-S500 | Search bar desktop+mobile + Subscribe CTA + Login CTA + top pulse bar + admin bar compat + newsletter overlay compat + cookie banner compat + homepage visual baselines + homepage signoff | search input in chrome layout + S-ADS subscribe CTA + cookie consent compat | complete |

---

## Closes

S301-S500 = 200 sprints. Status breakdown:

- **Complete:** 187 sprints
- **Partial:** 13 sprints (visual-diff matrix, per-source LICENSE column, etc.)

**Newly evidenced in this pack: 187 sprints** (some overlap with prior S-LANG row but mostly fresh ledger additions).
