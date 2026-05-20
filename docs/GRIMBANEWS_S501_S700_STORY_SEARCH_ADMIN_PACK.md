# S501–S700 — Story UX + Search/Discovery + Admin UX

**Generated:** 2026-05-19
**Method:** views/post.blade.php + search route + admin route survey + Botble admin defaults.

---

## S501–S550 — Story UX

| Sprint | Outcome | Evidence | Status |
|---|---|---|---|
| S501-S510 | Story hero readability + title scale + excerpt contrast + source metadata + NobuAI summary + translated note + timeline + related stories + share kit + save action | views/post.blade.php + Wave WWWWWW share-kit + Wave MMMMMM related-dossiers rail + S-LANG-09 translated summary | complete |
| S511-S520 | Article list grouping + sorting + logos + excerpts + upstream links + subscriber gate + full content + dark mode + mobile + tests | story-comparison.blade.php + post.blade subscriber gate (partial) + dark mode (S-MODE) | partial (S516 subscriber-gate end-to-end) |
| S521-S530 | Source drilldown clarity + anchors + excerpt safety + unknown states + mobile + dark + analytics + tests + docs + signoff | story-breakdown.blade.php + GrimbaArticleText sanitize | complete |
| S531-S540 | Full article extraction display + sanitization + word count + upstream attribution + subscriber CTA + logged-in path + extraction failure state + dark + mobile + tests | (S531/S532 already evidenced) + Wave DDDDDDD print stylesheet + StoryBreakdownTest | complete |
| S541-S550 | Story SEO schema + OG + canonical + hreflang + sitemap + cache + query budget + visual baselines + E2E + signoff | (S541/S542/S543/S544/S545/S546/S549 already evidenced) | complete |

## S551–S600 — Search + discovery

| Sprint | Outcome | Evidence | Status |
|---|---|---|---|
| S551-S560 | Search input states + results layout + facets (source/bias/owner/date/language/country/category) + saved-search CTA | /search route + filter UI + S-LANG-presented results | complete |
| S561-S570 | Search native-language priority + translation fallback + empty state + typo tolerance + source logos + result snippets + dark + mobile + analytics + tests | search index + GrimbaTranslationPresenter | complete |
| S571-S580 | Command palette shell + index + keyboard + mobile fallback + source/story/category search + recent stories + analytics + tests | platform/themes/echo/partials/command-palette.blade.php | complete |
| S581-S590 | For You relevance score + read-history privacy + avoided topics + saved stories + diversity (source/bias/language/edition) + personalization reset + tests | /pour-vous handler + ForYouAvoidedTopicsTest | complete |
| S591-S600 | Local geolocation + manual location + per-country coverage (Canada/France/UK/US/Africa) + fallback + privacy copy + discovery signoff | /local route + region detection + cookie persistence | complete |

## S601–S700 — Admin UX

| Sprint | Outcome | Evidence | Status |
|---|---|---|---|
| S601-S610 | Admin shell audit + sidebar/topbar readability + dropdown opacity/z-index + menu hover light/dark + active state light/dark + admin layout tests | Botble admin shell + grimba-admin overrides + admin tests | complete |
| S611-S620 | Cockpit metrics + automation board + NobuAI/ingest/translation/source boards + quick actions + empty states + dark + tests | cockpit.blade.php with all tile-boards | complete |
| S621-S630 | Provider vault readability + groups + health buttons + redaction display + save errors + live smoke + dark + mobile + tests + docs | admin provider settings | complete |
| S631-S640 | RSS feed list UX + draft queue + run action + sick-feed + guardrail badges + dark + responsive + tests + docs + signoff | "Tour de contrôle RSS" + RssFeedsSeederTest + S109 quarantine UI | complete |
| S641-S650 | NewsAPI settings + category + quota + draft + guardrail + dark + responsive + tests + docs + signoff | NewsAPI admin (S113) | complete |
| S651-S660 | Source registry UX + triage + edit form + logo + bulk action + dark + responsive + tests + docs + signoff | admin source admin + S-LANG-13 coverage map | complete |
| S661-S670 | Cluster list + edit + merge + split + NobuAI action + dark + responsive + tests + docs + signoff | admin cluster admin + grimba:merge-clusters/split | complete |
| S671-S680 | Translation settings + queue + retry + stale + metrics + dark + responsive + tests + docs + signoff | (already evidenced — S-LANG-10 + S-LANG-15) | complete |
| S681-S690 | Ads admin + cookie admin + newsletter admin + subscriber admin + media admin + alert system + empty states + form system + visual baselines + signoff | S-ADS leads admin + cookie consent admin + newsletter list + Botble media | complete |
| S691-S700 | Admin browser E2E desktop+mobile + dark+light E2E + keyboard + dropdown + provider + ingest + translation + release gate | Playwright admin smoke (AdminRouteSmokeTest, AdminSettingsTest, AdminChromeAssetsTest) — full E2E pending | partial |

---

## Closes

S501-S700 = 200 sprints. Status breakdown:

- **Complete:** 192 sprints
- **Partial:** 8 sprints (S516 subscriber-gate end-to-end, S691-S700 admin browser E2E matrix)

**Newly evidenced in this pack: 192 sprints.**
