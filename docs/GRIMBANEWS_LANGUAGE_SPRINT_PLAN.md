# GrimbaNews — Language & Translation Sprint Plan

> **Owner:** Steve (CPO design), Rajesh Kumar (backend), Nina Patel (frontend),
> Larry Ellison (schema), Sara Kim (QA), Zen/Echo/Mnemo (audit).
> **Mandate (Vader 2026-05-16):** "translation has not been functionning as
> intended … needs its own phased sprint planning of more than 237 sprint
> only for the language."
>
> **Scope:** GrimbaNews bilingual surface (FR ↔ EN) end-to-end. Pipeline,
> rendering, switching, fidelity, observability. Includes the new
> NobuTranslate.com upstream integration.

---

## Phase A — Diagnosis & Stabilization (Sprints 1–30)

The translation layer has accumulated drift. Phase A makes the bleeding stop
and produces a complete inventory of what's wrong before we rebuild anything.

| Sprint | Title | Acceptance |
|--------|-------|------------|
| A-01 | EN dossier layout regression — bias panel falling to bottom | Repro at `/article/{slug}` with `grimba_lang=en` cookie. Capture DOM order vs FR. Land the layout fix and document the trigger. |
| A-02 | New FR strings shipped without EN translations | Audit `platform/themes/echo/lang/fr.json` vs `en.json`. Every key present in fr must exist in en (auto-translated where needed). Add CI guard. |
| A-03 | Lang-cookie collision audit | `grimba_lang`, `app_locale`, Botble's `language`, browser Accept-Language — map precedence and document. Fix any path that loses the cookie value. |
| A-04 | Strings with hardcoded FR in PHP (no `__()` wrapper) | `grep -rn` across `app/`, `platform/themes/echo/`. Catalog every hardcoded string and ticket each into Phase D. |
| A-05 | Inline strings in Blade `style=` / `title=` attrs | Same hunt but inside HTML attributes. Common miss because IDE highlighting doesn't flag them. |
| A-06 | Date/time locale leak (Carbon) | `diffForHumans` calls without `->locale($lang)` chain — `grep -rn "diffForHumans"`. Each must respect the current request locale. |
| A-07 | Number / currency formatters | `number_format()` calls that should be `Number::format($value, locale: ...)` for EU vs US thousand separators. |
| A-08 | Pluralization audit | Every `trans_choice` call — confirm both FR and EN forms have the `:count form\|:count forms` shape and not just FR. |
| A-09 | URL slug locale-leak audit | French slugs (`/comparatif`, `/sources`, `/angles-morts`) rendering in EN context. Decide: EN aliases (`/comparison`, `/sources`, `/blindspots`) or keep FR slugs as canonical. |
| A-10 | SEO title / description locale | OG title + meta description need to respect locale. Audit `SeoHelper::set*` calls in `post.blade.php`, `index.blade.php`. |
| A-11 | Hreflang tag completeness | Each public route must emit `rel="alternate" hreflang="fr"` + `hreflang="en"` + `hreflang="x-default"`. |
| A-12 | Sitemap per-locale entries | sitemap.xml must list each post under both locale URLs. |
| A-13 | RSS feed per-locale | `/feed.xml` currently locale-mixed. Split into `/feed.fr.xml` and `/feed.en.xml`. |
| A-14 | Cron job: translate-pending coverage gap | Audit `GrimbaTranslatePending` — confirm it picks up EVERY new published post in both directions, not just one. |
| A-15 | Cron job: missing translated_content | Posts with translated title but empty `translated_content` — surface count + backfill. |
| A-16 | NobuAI summary locale lock | `summary_nobuai` column is a single text. Add `summary_nobuai_locale` so we never display the wrong language to a reader. |
| A-17 | Bias-tier labels locale | `App\Ground\Bias::label()` and friends — confirm FR/EN both wired. |
| A-18 | Source country labels locale | `GrimbaSourceBreakdown::countryLabel()` and `originLabel()` — same audit. |
| A-19 | Story breakdown extractive lead-sentence locale | When extractive mode runs, the lead sentence must come from the locale-appropriate field, not blindly the source language. |
| A-20 | Translation provenance chip on every translated surface | If a card shows a translated headline, the NobuAI chip must be present. Audit every card variant. |
| A-21 | Translation toggle on dossier | Per-dossier "Show original" toggle. Stub in place but verify it doesn't break the article-list / dossier-voices state machine. |
| A-22 | Admin: translation failures table | `grimba_translation_failures` table is migrated but never inspected by editors. Surface in admin. |
| A-23 | Admin: translation runs table | `grimba_translation_runs` — same. |
| A-24 | `--to=fr` / `--to=en` parity guard | `routes/console.php` schedules both; verify both fire and produce roughly balanced output volume. |
| A-25 | Locale-respecting URL helper | `url('/article/{slug}')` strips locale. If we adopt locale prefixes, every route helper must inject. |
| A-26 | Source language detection accuracy | Posts with `original_language` mis-detected (e.g. ES classified as FR). Audit sample of 50 posts per direction. |
| A-27 | Email digest locale | Vault digest + saved-search digest must respect the recipient's locale preference. Currently FR-only. |
| A-28 | Newsletter unsubscribe locale | Same audit. |
| A-29 | Translation health snapshot dashboard | One-page admin view showing pending/failed counts per direction per day. |
| A-30 | Phase A close: blocker list to Steve | Hand off the cleaned inventory + must-fix-now list. Steve signs the Phase B kickoff. |

---

## Phase B — NobuTranslate.com HTTP Driver (Sprints 31–70)

GrimbaNews' upstream becomes `nobutranslate.com` via its public HTTP API.
The legacy module-class driver (`Modules\NobuTranslation\Support\NobuTranslator`)
and the direct-vendor chain (DeepL/OpenAI/Mistral/etc.) become fallbacks only.

| Sprint | Title | Acceptance |
|--------|-------|------------|
| B-01 | Contract docs locked | Read `/Users/vb/nobutranslate/routes/api.php` + `TranslateController` — document the `source` / `target_locale` / `source_locale` / `provider` shape on a fixed page. |
| B-02 | `nobutranslate` driver added to GrimbaTranslator::CHAIN | First position. `NOBUTRANSLATE_API_URL` + `NOBUTRANSLATE_API_KEY` env vars. |
| B-03 | `credentialFor('nobutranslate')` wired | URL + key both required for the driver to advertise as configured. |
| B-04 | `viaNobuTranslate` dispatch method | HTTP POST `/v1/translate` with Bearer header. Response shape `{"translated": ...}`. |
| B-05 | Failover on 502 / 503 / 504 | NobuTranslate hiccup must drop through to the next configured driver. |
| B-06 | Quota exhausted (429) handling | Translate quota over → fall through silently to fallback chain + log a warning in `GrimbaAutomationMonitor`. |
| B-07 | Provider pin option | `NOBUTRANSLATE_PROVIDER=openai` env routes the upstream's `provider` field. Admin can override per-tick. |
| B-08 | Batch endpoint integration | `/v1/translate/batch` — `GrimbaTranslatePending` should batch up to 20 texts/call to cut overhead. |
| B-09 | Timing telemetry | Per-call latency / driver / provider captured in `grimba_translation_runs`. |
| B-10 | Idempotency key | Send a stable key so a retried request doesn't get billed twice on NobuTranslate. |
| B-11 | API key rotation flow | Admin can drop a new key in settings without redeploy; key resolution chain: setting → env. |
| B-12 | Customer plan awareness | NobuTranslate returns plan headers; surface remaining quota in admin. |
| B-13 | Usage page in admin | Mirror of NobuTranslate's `/v1/usage` for the GrimbaNews tenant. |
| B-14 | Connection pooling | Single Guzzle/HTTP client instance across calls — re-use TCP for ingest bursts. |
| B-15 | Circuit breaker | After 5 consecutive failures, mark `nobutranslate` cold for 5min and use fallbacks. |
| B-16 | Recovery probe | Cold breaker probes `/v1/health` every 60s before re-enabling. |
| B-17 | Local-dev fixture | `NOBUTRANSLATE_API_URL=http://127.0.0.1:8003` for dev. Document. |
| B-18 | Webhook for completed batches | If NobuTranslate adds a "translation complete" webhook, wire it. |
| B-19 | Per-locale provider preference | Admin can pin "EN→FR uses deepl, FR→EN uses openai". |
| B-20 | Cost ceiling | Admin sets a daily $ ceiling; ceiling hit → translation pauses for the day. |
| B-21 | Drop the legacy `Modules\NobuTranslation` shim | Once `nobutranslate` HTTP is stable for 7 days, remove the unused module class path. |
| B-22 | Admin health card | "NobuTranslate · OK · 23ms · 4,128 chars today" card on the cockpit. |
| B-23 | Force-refresh button | Admin can re-translate any post on demand. |
| B-24 | Bulk re-translate by category | Admin can re-translate every Politique article in one click. |
| B-25 | Plan upgrade flow | UI prompt when GrimbaNews approaches NobuTranslate plan ceiling. |
| B-26 | Failure email digest | Daily summary of translation failures emailed to Vader. |
| B-27 | Provider attribution surface | UI chip "translated via NobuAI" wraps the actual upstream driver name (never leaks "OpenAI"/"Claude" — NobuAI brand rule from CLAUDE.md). |
| B-28 | Latency budget | p95 < 1.5s per single-call translation. Alert when exceeded. |
| B-29 | Edge case: HTML preservation | Translating a post body with `<a>` tags must preserve them. Add tests. |
| B-30 | Edge case: long-form chunks | Posts > 5000 chars chunked then reassembled. |
| B-31 | Edge case: code blocks | `<pre>` / `<code>` must NOT be translated. |
| B-32 | Edge case: quoted material | Verbatim quotes inside articles should stay original — flag for editor. |
| B-33 | NobuAI brand scrub on response | Sanity scan on every response to catch any "OpenAI/Anthropic/Claude" leak from a misbehaving upstream provider, replace with "NobuAI". |
| B-34 | Multi-tenant readiness | If we host other Iboga products, GrimbaTranslator must accept per-tenant keys. |
| B-35 | Retry with jitter | Failed call → exponential backoff with jitter, max 3 retries. |
| B-36 | Translation cache layer | Identical text+lang pair within 24h returns cached translation, no upstream call. |
| B-37 | Cache eviction policy | LRU 10k entries; admin can flush. |
| B-38 | Test: contract test against NobuTranslate fixture | Mock the API in tests so CI doesn't burn quota. |
| B-39 | Test: chaos test (random 503s) | Verify failover triggers and final output is still usable. |
| B-40 | Phase B close: Zen audit | NobuTranslate driver code-reviewed end-to-end. Ship to prod. |

---

## Phase C — Layout / SSR Locale Safety (Sprints 71–100)

Every layout must render correctly in both locales without flow regressions.

| Sprint | Title | Acceptance |
|--------|-------|------------|
| C-01 | Dossier sticky aside in EN | Fix the "bias falls to bottom" regression Vader saw. Repro + diagnose + ship. |
| C-02 | Locale-aware grid breakpoints | Some FR strings are 30% longer than EN equivalents. Confirm all card grids handle both. |
| C-03 | Topic chips overflow | Long FR category names wrap correctly on mobile; same with EN. |
| C-04 | Footer columns | FR footer is 4 columns; verify EN fits without wrap collapse. |
| C-05 | Hero kicker badge | New "Histoire phare" kicker — confirm EN label fits the same width. |
| C-06 | Three Voices panel min-width | EN excerpts can be much shorter; ensure quote card doesn't visually collapse. |
| C-07 | Spectrum field labels | "Gauche / Centre / Droite" axis labels — EN equivalents on parity. |
| C-08 | Section heads | "Rubrique" eyebrow + Fraunces section title — EN equivalents. |
| C-09 | Briefing column threaded medallions | "Lu chez" jump-list — EN parity. |
| C-10 | Coverage bar legend chips | All percentages render with appropriate locale separator. |
| C-11 | Save button label | "Sauvegarder" / "Save" — chip width matches. |
| C-12 | NobuAI chip text | "Traduit par NobuAI" / "Translated by NobuAI" — fit. |
| C-13 | Cookie consent banner | Both translations of the 3-button consent fit on one mobile row. |
| C-14 | PWA install prompt | Both locales. |
| C-15 | Newsletter modal | Title + body + CTA all fit. |
| C-16 | Translation toggle pill on dossier | "Voir l'original" / "Show original" UI parity. |
| C-17 | Vault FAB tooltip | Bilingual. |
| C-18 | Mobile bottom nav labels | Bilingual + truncation rules. |
| C-19 | 404/500/503 pages | Bilingual. |
| C-20 | Search page placeholders | Bilingual. |
| C-21 | For You feed labels | Bilingual. |
| C-22 | Member area | Bilingual. |
| C-23 | Account / login / register | Bilingual. |
| C-24 | Admin chrome (internal) | Stay FR-only OR enable EN for international admins. Decide. |
| C-25 | Reading time chip "≈ 4 min" | Bilingual. |
| C-26 | Date format | French = "16 mai 2026"; English = "May 16, 2026". |
| C-27 | Time zone display | Stay UTC OR honor reader's TZ. Decide. |
| C-28 | RTL prep (Arabic) | Layout audited for RTL — only structural prep, no Arabic locale yet. |
| C-29 | Print stylesheet | Bilingual. |
| C-30 | Phase C close: visual regression run | Playwright snapshot every public route in FR and EN, diff against baseline. |

---

## Phase D — String Coverage Completeness (Sprints 101–140)

Every translatable surface in the app must have a complete EN translation.

| Sprint | Title | Acceptance |
|--------|-------|------------|
| D-01 | Lang JSON audit script | Tool that lists every `__('...')` key in PHP/Blade and reports missing en.json entries. |
| D-02 | Add to CI | Script blocks merge if any FR key has no EN counterpart. |
| D-03 | Backfill from this session's new strings | dossier-voices, story-page__bar, insights-panel — every key added gets an EN translation. |
| D-04 | Home partials sweep | hero-grid, all-sides-rail, most-read-by-bias, top-news, section-blocks, latest-plus-topics. |
| D-05 | Story partials sweep | bias-distribution, article-list, source-drilldown, coverage-details, timeline, highlights, voices, share-kit. |
| D-06 | Topic / category page sweep | category.blade.php, tag.blade.php, search.blade.php. |
| D-07 | Comparison page sweep | comparison.blade.php, comparison-index.blade.php. |
| D-08 | Methodology / FAQ / About | methodology.blade.php, faq.blade.php, about.blade.php. |
| D-09 | Sources directory | sources.blade.php, source.blade.php. |
| D-10 | Blindspot page | blindspot.blade.php. |
| D-11 | Member pages | account.blade.php, member/* views. |
| D-12 | Author pages | author.blade.php. |
| D-13 | Galleries | galleries.blade.php, gallery.blade.php. |
| D-14 | Loop / video | loop.blade.php. |
| D-15 | Coffre (vault) | coffre.blade.php, coffre-share.blade.php. |
| D-16 | Local geo picker | local.blade.php. |
| D-17 | Advertise page | advertise.blade.php. |
| D-18 | Footer-dark | footer-dark.blade.php. |
| D-19 | Header / nav | main-header.blade.php, main-menu.blade.php, main-menu-mobile.blade.php. |
| D-20 | Command palette | command-palette.blade.php. |
| D-21 | All form labels + placeholders | Forms must have bilingual labels and ARIA attrs. |
| D-22 | All error messages | Backend validation messages bilingual via Laravel's `lang/en/validation.php`. |
| D-23 | Email templates | All system emails bilingual. |
| D-24 | Console commands signatures / descriptions | Stay EN — internal. |
| D-25 | Admin tooltips | Stay FR — internal. |
| D-26 | Public API responses | error keys bilingual, payload locale-aware. |
| D-27 | JSON-LD schema fields | description + headline in correct locale. |
| D-28 | Meta robots / sitemap | Locale-aware. |
| D-29 | URL slugs / route names | Decision-and-implement per A-09. |
| D-30 | Aria-labels | Bilingual on every interactive element. |
| D-31 | Screen-reader-only text | `sr-only` strings translated. |
| D-32 | Image alt text | Generated alts (post-hero-img fallback) bilingual. |
| D-33 | Loading states / skeletons | "Loading…" bilingual. |
| D-34 | Empty states across the site | One pass on every empty-state copy. |
| D-35 | Toast / flash notifications | All bilingual. |
| D-36 | Push notification copy | If/when PWA push lands. |
| D-37 | Onboarding modal | Bilingual including step copy. |
| D-38 | Cookie consent — granular controls | Each cookie category description bilingual. |
| D-39 | Translation chip itself ("traduit par NobuAI") | Bilingual. |
| D-40 | Phase D close: zero missing keys | CI guard enforces. |

---

## Phase E — UX, Switcher, Provenance (Sprints 141–170)

The locale switcher itself, fidelity signals, and reader feedback loops.

| Sprint | Title | Acceptance |
|--------|-------|------------|
| E-01 | Locale switcher visual polish | Steve-styled toggle in header. Animated pill. |
| E-02 | Mobile locale switcher | Compact icon button on mobile header. |
| E-03 | Persistence indicator | After switching, a brief toast: "FR/EN preference saved." |
| E-04 | First-visit locale detection | Honor `Accept-Language` for first request, then cookie wins. |
| E-05 | Per-post original-language badge | Card shows "FR" / "EN" pill indicating source language. |
| E-06 | Translation quality chip | Auto-translated articles carry a confidence chip (high/medium/low). |
| E-07 | "View original" toggle on every translated card | Inline; flips card content without nav. |
| E-08 | "Report bad translation" flow | One-click on any translated surface → opens a contact form pre-filled with post ID + locale. |
| E-09 | Editor "fix translation" admin UI | Editor sees flagged translations and can override. |
| E-10 | Per-locale read receipts | Vault analytics differentiate FR-read vs EN-read events. |
| E-11 | Locale-aware search | EN query against EN body, FR query against FR body, mixed via OR. |
| E-12 | Auto-complete bilingual | Search suggestions in active locale. |
| E-13 | Locale-aware favorites | Vault saves the locale you read in; restores it on re-open. |
| E-14 | Reading history locale | `grimba_read` cookie stores locale at time of read. |
| E-15 | Bilingual breadcrumbs | Crumbs translate. |
| E-16 | Bilingual editorial categories | Category names translatable (Politique → Politics). |
| E-17 | Bilingual tags | Same. |
| E-18 | "Translated headline" vs "original headline" disclosure | Reader can hover to see original. |
| E-19 | Translation provenance footer | At article bottom: "This was translated by NobuAI from FR. Original published at LeMonde.fr." |
| E-20 | Quote integrity rule | Quoted verbatim material inside an article must show original in `<lang>` tag with optional translation in parens. |
| E-21 | Locale-aware compare-side-by-side | If revived, must show both panels in the active locale. |
| E-22 | Locale-aware OG images | `/og/post/{id}.png` respects locale param. |
| E-23 | Locale-aware sitemaps | per-locale entries. |
| E-24 | Locale-aware RSS feeds | per-locale. |
| E-25 | Locale-aware newsletter subscription | recipient pref persisted. |
| E-26 | Bilingual translation note partial | translation-note.blade.php audit. |
| E-27 | "Available in your language" prompt | If a reader on EN lands on an article only in FR, prompt to translate. |
| E-28 | "Original available" prompt | Reverse direction. |
| E-29 | Reading-time recalc | EN word count ≠ FR word count; recalc per locale. |
| E-30 | Phase E close: reader UX review | Steve signs off on the bilingual reader experience. |

---

## Phase F — Performance / Cache (Sprints 171–195)

| Sprint | Title | Acceptance |
|--------|-------|------------|
| F-01 | Translation cache table | Migration: `grimba_translation_cache (hash, source_lang, target_lang, text, created_at)`. |
| F-02 | Hash strategy | sha1(text + source + target). |
| F-03 | Hit-rate dashboard | Cockpit shows cache hit rate per day. |
| F-04 | TTL + LRU eviction | 10k cap, eviction policy documented. |
| F-05 | Cache flush per source | When a source updates an article, flush its translation cache row. |
| F-06 | Batch insert / read | One round trip per ingest tick. |
| F-07 | Edge cache (Cloudflare) of public translated views | Hit ratio target ≥ 80%. |
| F-08 | Cache invalidation on re-translate | Editor-triggered refresh purges. |
| F-09 | Lazy translation on demand | Don't translate every post — only those that get traffic in the target language. |
| F-10 | Background warmer | Post-publish hook warms both locales for popular sources. |
| F-11 | Backpressure | If NobuTranslate queue depth high, defer non-essential translations. |
| F-12 | Cost per translation tracking | Per provider, per locale pair. |
| F-13 | Daily cost report | Surfaced to Vader. |
| F-14 | Latency p99 SLO | Define and alert. |
| F-15 | Long-tail content translation policy | Posts older than 90 days only translated on demand. |
| F-16 | Failover budget | If NobuTranslate down, allow direct-driver path with explicit log. |
| F-17 | DB query optimization | Translation lookups must hit `translated_*` columns with proper indexes. |
| F-18 | Index migration | `posts.translated_to`, `posts.original_language` indexes. |
| F-19 | Eager-load translation columns | Allocator queries already SELECT them; verify partials don't re-query. |
| F-20 | Avoid double-translation | Detect FR-source post that got mis-translated and skip. |
| F-21 | Memory ceiling on long bodies | Translate in chunks, stream. |
| F-22 | Gzip on /v1/translate request bodies | Reduces upstream egress. |
| F-23 | HTTP/2 multiplexing | Verify upstream supports it. |
| F-24 | Connection keep-alive | Long-lived TCP between cron worker and NobuTranslate. |
| F-25 | Phase F close: load test | 1k posts translated in 60s without circuit-breaker trips. |

---

## Phase G — QA / E2E (Sprints 196–220)

| Sprint | Title | Acceptance |
|--------|-------|------------|
| G-01 | Playwright FR home snapshot | Pixel-diff baseline. |
| G-02 | Playwright EN home snapshot | Same. |
| G-03 | Playwright FR dossier snapshot | One representative cluster. |
| G-04 | Playwright EN dossier snapshot | Same cluster. |
| G-05 | Locale-switch flow test | Switch FR→EN, every visible string changes. |
| G-06 | Locale-switch flow test reverse | EN→FR. |
| G-07 | Cookie persistence test | New tab inherits locale. |
| G-08 | Search FR/EN parity test | Same query returns parallel results. |
| G-09 | Newsletter signup FR/EN test | Form submits, confirmation in correct locale. |
| G-10 | Vault save+share FR/EN | Cookie-based vault survives locale switch. |
| G-11 | Onboarding modal FR/EN | Locale-correct steps. |
| G-12 | Translation fallback test | NobuTranslate 503 → DeepL → finished. |
| G-13 | Cache hit test | Same text+lang pair second call → cached. |
| G-14 | Quota exhaustion test | 429 from upstream → log + fallback. |
| G-15 | NobuAI brand scrub test | Mock upstream returns "translated by OpenAI" → output replaces with "NobuAI". |
| G-16 | Hreflang assertion test | Every public route emits correct hreflang. |
| G-17 | Sitemap FR/EN test | Both lists present + valid XML. |
| G-18 | RSS FR/EN test | Both feeds present + valid. |
| G-19 | JSON-LD locale test | Schema fields in active locale. |
| G-20 | OG / Twitter card locale test | Image + title respect locale. |
| G-21 | Email locale test | Vault digest renders both locales. |
| G-22 | Accessibility audit FR | axe-core run, zero criticals. |
| G-23 | Accessibility audit EN | Same. |
| G-24 | Lighthouse perf FR + EN | Both ≥ 90 on home. |
| G-25 | Phase G close: ship gate | All G tests must pass for any future release. |

---

## Phase H — Future Locales (Sprints 221–245)

| Sprint | Title | Acceptance |
|--------|-------|------------|
| H-01 | Locale registry abstraction | Move from hardcoded `[fr, en]` to a config-driven list. |
| H-02 | ES (Spanish) scaffolding | lang/es.json stub, locale switch picks it up. |
| H-03 | ES NobuTranslate testing | Verify upstream supports FR↔ES and EN↔ES. |
| H-04 | ES launch — Politique only | One category lit up in Spanish. |
| H-05 | ES launch — full site | After Politique stabilizes. |
| H-06 | PT-BR scaffolding | lang/pt-BR.json. |
| H-07 | PT-BR NobuTranslate | Verify. |
| H-08 | PT-BR launch | Phased. |
| H-09 | DE scaffolding | lang/de.json. |
| H-10 | DE NobuTranslate | Verify. |
| H-11 | DE launch | Phased. |
| H-12 | AR (Arabic) scaffolding | RTL layout. |
| H-13 | AR NobuTranslate | Verify. |
| H-14 | AR launch — Politique only | Smaller rollout first. |
| H-15 | Locale-specific editorial categories | Some categories may not translate 1:1 across cultures. |
| H-16 | Locale-specific source mix | "African Politique" feed for EN-AF readers; etc. |
| H-17 | Locale-specific bias norms | The bias convention may need to flip per market (per `feedback_steve_design_language` toggle). |
| H-18 | Locale-specific currency / number formats | Across all surfaces. |
| H-19 | Locale-specific date display | Including non-Gregorian where relevant. |
| H-20 | Per-locale onboarding | Different first-visit story per market. |
| H-21 | Per-locale newsletter cadence | Some markets prefer daily, some weekly. |
| H-22 | Per-locale OG image variants | Maybe French shows Liberté/Égalité framing, English doesn't. |
| H-23 | Cross-locale recommendations | "Available in ES" prompt when a post has been translated. |
| H-24 | Multi-locale admin tooling | Editor can switch interface locale separately from reader locale. |
| H-25 | Phase H close: 5-locale launch announcement | FR / EN / ES / PT-BR / DE live. |

---

## Cross-Phase Standing Rules

1. **NobuAI brand inviolable.** Every user-facing string MUST say "NobuAI"
   — never any external provider name. Phase B-33 audit catches leaks; the
   sanitisation pass on every NobuTranslate response is the safety net.
2. **Reduced motion respected.** Every locale-related animation gates on
   `prefers-reduced-motion`.
3. **No `git add -A`.** Each commit stages specific files per `feedback_git_add_specific`.
4. **Push before deploy.** Every coherent unit pushes to `darkvaderfr/grimbanews:main`
   before any prod touch.
5. **Dream-team audit panel runs** on every non-trivial commit per
   `feedback_dream_team_audit`: Zen / Echo / Mnemo in parallel.
6. **Mythos cadence** — alternate audit / big / polish per
   `feedback_sprint_cadence_audit_big_polish`.

---

## Roster

| Seat | Real name | Discipline this sprint stream |
|---|---|---|
| Backend lead | Rajesh Kumar | NobuTranslate driver, failover, cache, batch |
| Backend support | Lisa Nguyen | Cron + admin endpoints |
| Schema | Larry Ellison | translation tables, indexes, cache layer |
| Frontend lead | Nina Patel | Switcher polish, dossier locale-safety, RTL prep |
| UI/UX | Alex Morgan | Pill switcher, fidelity chip, "report translation" flow |
| Product / Design | Steve Jobs | Cross-locale reader experience, brand-voice consistency |
| PM | Liam Smith | Phase ordering, dependency tracking |
| QA | Sara Kim | Playwright snapshots, E2E flows |
| Infra | Jacob Lee | Edge cache, perf tests |
| Audit | Zen / Echo / Mnemo | Per-commit panel |
| Oversight | Lucy Leai (strategy), Sara Chen (privacy), Ray Dalio (cost ceiling), Zenkai (signoff) | Phase gates |

---

## Status

- **Plan locked:** 2026-05-16 (Vader directive)
- **Phase A kickoff:** awaiting first sprint claim
- **Phase B prerequisite:** A-30 closed
- **Phase B-02 reverted in flight (2026-05-16):** the `nobutranslate` CHAIN
  entry was started in this session, then backed out before commit because
  the plan now governs that work.
- **First bug already filed:** A-01 (EN dossier — bias falls to bottom),
  A-02 (new FR strings shipped without EN), A-04 (hardcoded FR audit
  pending). These will be the opening three sprints.

> **245 sprints across 8 phases. Owned by the Iboga Ventures roster.
> Translation is treated as a first-class product surface, not a side-effect.**
