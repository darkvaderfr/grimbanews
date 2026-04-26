# MYTHOS — GrimbaNews Comprehensive Sprint Fleet

**Owner:** Vader · **Drafted:** 2026-04-26 (end of Session ~4) · **Status:** living document

This is the master sprint registry for GrimbaNews — Iboga Ventures' francophone
Ground News equivalent (Echo theme on Botble CMS, repo `darkvaderfr/grimbanews`,
local at `/Users/vb/GrimbaNews/`). Use it as the single source of truth for
"what's done, what's queued, why each fleet exists." All sprint IDs are
S-prefixed integers, ordered chronologically.

The fleet is grouped by theme rather than chronology so each fleet can be
worked end-to-end. Inside a fleet, items are ordered by dependency.

---

## 0 · Glossary

- **Story page** = `/blog/{slug}` when the post belongs to a `story_cluster_id`
  with ≥ 2 published posts. Single-cluster (orphan) posts use the legacy layout.
- **Cluster** = group of posts about the same news event, joined by
  `posts.story_cluster_id`. Built by `GrimbaClusterMatcher` during ingest.
- **Vault** = client-side bookmark store in the `grimba_vault` cookie (CSV of
  post ids, last-saved-first, capped at 50). Surfaced at `/coffre`.
- **Bias buckets** = `left`, `center`, `right`, `unknown` — set on each post
  from its source's `bias_rating`. Color language used everywhere:
  blue `#3b82f6` / grey `#a8a8a8` / red `#e84c3d` / muted `rgba(26,23,19,0.45)`.
- **Steve-styled** = grimba-chrome layout + Fraunces title + Public Sans body +
  paper bg + glass-panel cards + cinematic spacing. Mandatory for every
  user-facing page (per Vader's design-language memory).
- **NobuAI** = the only LLM brand exposed to readers. Anthropic / OpenAI /
  Gemini are never named in user-facing surfaces.

---

## Fleet Index

| Fleet | Theme | Sprints | Status |
|-------|-------|---------|--------|
| **A** | Push & Deploy Gate | A1–A5 | OPEN — pushing blocked |
| **B** | Story Page Completion | B1–B10 | 10/10 done |
| **C** | Vault Maturity | C1–C8 | 4/8 done |
| **D** | Discovery & Navigation | D1–D8 | 5/8 done |
| **E** | NobuAI Integration | E1–E7 | 0/7 (gated on API key) |
| **F** | Performance & SEO | F1–F8 | 8/8 done |
| **G** | A11y & I18n | G1–G6 | 4/6 done |
| **H** | Testing & QA | H1–H7 | 0/7 |
| **I** | Marketing & Growth | I1–I8 | 0/8 |
| **J** | Admin & Editorial Tooling | J1–J6 | 0/6 |

---

## Already-Shipped Reference (chronological)

These sprints are **DONE and committed**. Listed for handoff continuity. Most
are landed in commits between `df5e078` (S170) and `1e64da5` (S184).

| Sprint | Title | Commit | Notes |
|--------|-------|--------|-------|
| S163 | Full-article extractor (`GrimbaArticleExtractor`) | (earlier) | DOMDocument-based scoring; populates `posts.full_content` for paid-tier reading |
| S165 | Categories overhaul (`GrimbaCategoryClassifier` + 15 FR news cats) | (earlier) | Replaces "Uncategorized/Videos/Healthy" placeholders |
| S166 | Steve-styled member auth pages | (earlier) | login + register + forgot + reset all wear grimba-chrome |
| S167 | Local news (`/local`) | (earlier) | IP geo cascade (ip-api → ipapi) + manual entry; cookies `grimba_local_*` |
| S168 | "Mon compte" landing | (earlier) | Replaces Botble's authoring sidebar with reader dashboard |
| S170 | Drop translation feature | `df5e078` | Removed picker/cookies/Translator; `translated_*` columns kept dormant |
| S171 | Story-page article-list polish | `c12b90c` | Source meta preloaded (whereIn), ownership chips, credibility tracker |
| S172 | Dark-mode coverage sweep | `c12b90c` | Section #14 in `grimba-home.css`; bg-white removed; `grimba_theme` cookie added to EncryptCookies::except |
| S173 | Save-for-later vault (cookie-only) | `6c0f25e` + `223e593` | `/coffre` route + `save-button` partial (icon + pill) + global JS handler + cookie added to EncryptCookies::except |
| S175 | Multi-source extractive synthesis | `223e593` | Lead sentence per cluster source, dedupe by 40-char prefix, badge label flips to "Synthèse multi-sources" + footnote |
| S176 | Region picker subtle translucency | `223e593` | rgba(246,241,232,0.94) + blur(10px) saturate(118%) — light + dark |
| S178 | Vault count badge in header | `3be887c` | SSR from `grimba_vault`, live JS update via `paintCount()` |
| S179 | Reading-time chip | `be2dd0b` | New `partials/reading-time.blade.php`, falls back full_content → content → description, suppresses below 30 words |
| S180 | Story timeline panel | `bf019fc` | Sidebar chronology, bias-colored dots, only fires when cluster ≥ 3 posts |
| S181 | One-sided coverage callout | `7a64b78` | Auto-derived from `$__gnByBias`; shows "Couverture déséquilibrée" warning |
| S182 | Vault CSV export | `92fd5e9` | `/coffre/export.csv` mirrors `/pour-vous/export.csv` shape |
| S183 | Bias-color dot per synthesis bullet | `b57cedd` | L=blue / C=grey / R=red on each lead-sentence bullet |
| S184 | Bias filter tabs on `/coffre` | `1e64da5` | Tous/Gauche/Centre/Droite client-side filter + per-bucket counts |

---

## Fleet A — Push & Deploy Gate

**Why:** Vader's CLAUDE.md mandates `local → darkvaderfr → prod` cadence with no
exceptions. Nine commits (S173–S184) are stacked locally and the standing
push-permission rule was scoped to Incognito only, blocking direct push to
`darkvaderfr/grimbanews:main`.

| ID | Sprint | Acceptance |
|----|--------|------------|
| A1 | Authorize push or open PR for fleet S173–S178 (vault foundation) | Either commits land on `origin/main`, or a PR exists with green CI |
| A2 | Authorize push or open PR for fleet S179–S184 (story polish + vault filter) | Same as A1, separate PR if PR-route chosen |
| A3 | Deploy to VPS via post-push pipeline | `https://grimbanews.com` (or staging) reflects S184 |
| A4 | Live smoke: /coffre, /local, story page, /login, /coffre/export.csv, region picker | Manual in-browser pass |
| A5 | Update `project_grimbanews_next_prompt.md` with prod sha | Memory committed |

---

## Fleet B — Story Page Completion

**Why:** Vader's brief: "fully replicate ground news article display". The
hero, article list, distribution, and timeline are in place; remaining gaps
are the orphan-post layout, Highlights/Voices panels, and sort options.

| ID | Sprint | Acceptance |
|----|--------|------------|
| ✅ S148 | Cluster article list grouped by bias with filter tabs | shipped earlier |
| ✅ S171 | Source meta + ownership/credibility chips on each card | `c12b90c` |
| ✅ S180 | Timeline panel | `bf019fc` |
| ✅ S181 | One-sided coverage callout | `7a64b78` |
| ✅ S183 | Bias-colored synthesis bullets | `b57cedd` |
| ✅ S175 | Multi-source extractive synthesis | `223e593` |
| ✅ S200 | Orphan-post layout polish | Uncommitted worktree |
| ✅ S185 | Highlights panel | Uncommitted worktree |
| ✅ S186 | Voices panel | Uncommitted worktree |
| ✅ S187 | Article-list sort toggle | Uncommitted worktree |
| ✅ S188 | Coverage gap detail link | Uncommitted worktree |
| ✅ S189 | Story share kit | Uncommitted worktree |
| ✅ S190 | "Lu chez X" jump-list | Uncommitted worktree |
| ✅ S201 | Reading-progress bar | Uncommitted worktree |
| ✅ S202 | Bias confidence indicator | Uncommitted worktree |
| ✅ S203 | Opaque region picker | Uncommitted worktree |
| ✅ S204 | FR-mode page translation + expanded NobuAI providers | Uncommitted worktree |
| ✅ S205 | Story-page Open Graph upgrade | Uncommitted worktree |

---

## Fleet C — Vault Maturity

**Why:** S173 shipped the cookie + UI; the feature is functional but
missing the polish that makes saved-articles habitual.

| ID | Sprint | Acceptance |
|----|--------|------------|
| ✅ S173 | Vault foundation (cookie + button + /coffre + chrome JS) | `6c0f25e`/`223e593` |
| ✅ S178 | Header vault count badge | `3be887c` |
| ✅ S182 | Vault CSV export | `92fd5e9` |
| ✅ S184 | /coffre bias filter tabs | `1e64da5` |
| ✅ S191 | Onboarding modal mention | Uncommitted worktree |
| ✅ S192 | Mobile floating action button | Uncommitted worktree |
| ✅ S193 | Keyboard shortcut "S" | Uncommitted worktree |
| ✅ S194 | Vault-share link | Uncommitted worktree |
| ✅ S195 | Stale-id pruning | Uncommitted worktree |
| ✅ S196 | "Marquer comme lu" | Uncommitted worktree |
| **C7** | **Save → email alert (member-only)** — `auth('member')` users get an option to email themselves the saved article digest weekly. Stores opt-in on `members.weekly_vault_digest` boolean column | New migration + scheduled command + opt-in toggle on /account |
| **C8** | **Vault analytics** — log save toggles to a `vault_events` SQLite table (event, post_id, ts, ip_hash) so editorial can see "what readers actually save" without per-user identification | Cron-archived weekly to `storage/exports/vault_events_YYYY-MM.csv` |

---

## Fleet D — Discovery & Navigation

**Why:** Most readers won't go past the homepage; the discovery surfaces
need to feel as cinematic as the story pages.

| ID | Sprint | Acceptance |
|----|--------|------------|
| ✅ S176 | Region picker subtle translucency | `223e593` |
| ✅ S197 | /sources index polish | Uncommitted worktree |
| **D2** | **/pour-vous polish** — bias-mix block already there; add a "Sujets que vous évitez" section showing categories with 0 reads in the last 14 days, link to `/blog?categorie=X` | Renders when read history > 10 articles |
| ✅ S206 | Trending kicker on homepage | Uncommitted worktree |
| ✅ S199 | Mobile floating bottom nav | Uncommitted worktree |
| **D5** | **Search facets** — `/search?q=...` already supports `source` and `bias` filters; add `from_date`, `to_date`, `owner` (drop the `news_sources.owner_name` enum). Update `search.blade.php` facet UI | Date range filter narrows results correctly |
| ✅ S207 | Topic-chip strip persistence | Uncommitted worktree |
| **D7** | **Saved-search alerts (member-only)** — let logged-in readers save a search query + facet combo. New `saved_searches` table; weekly cron emails new matches | One member can save & receive a digest |
| **D8** | **Site-wide command palette (⌘K)** — fuzzy search across categories, sources, recent stories. Lazy-loaded JS, cookie-cached search index | ⌘K opens; typing 2 chars surfaces matches |

---

## Fleet E — NobuAI Integration

**Why:** S175's extractive synthesis is honest about being baseline. Once a
NobuAI key lands, swap to true LLM summaries. All sprints below are gated on
`grimba_nobuai_active` setting + a working provider key.

| ID | Sprint | Acceptance |
|----|--------|------------|
| **E1** | **Schema migration** — add `posts.summary_nobuai TEXT NULL` + `summary_generated_at TIMESTAMP NULL` + index on `summary_generated_at` | Migration runs on fresh + existing DB |
| **E2** | **`GrimbaNobuaiSummarizer` service** — provider-agnostic interface; first impl uses Anthropic / OpenAI key from `nobuai.providers.*` config (NOT user-facing). Server logs may name provider; user-facing labels are "NobuAI" only | Service generates a 5-bullet summary for a 3-source cluster in < 8 s |
| **E3** | **Cron `grimba:summarize-clusters`** — every 15 min, find clusters with ≥ 3 published posts, no `summary_nobuai`, updated in last 24h. Generate + persist | Cron picks up at least one cluster on a fresh run |
| **E4** | **Story-hero swap** — when `$post->summary_nobuai` is non-null, badge label flips to "Insights par NobuAI", bullets show LLM output, footnote disappears | Verified on a cluster with a generated summary |
| **E5** | **Per-source bias detection (LLM)** — replace `news_sources.bias_rating` editorial flag with NobuAI auto-classification when score < 50 (mark with subscript per B9) | Backfill command exists; 10 sources reclassified |
| **E6** | **NobuAI translation re-light (optional)** — re-introduce translation as a **per-paragraph** opt-in on the story page (not site-wide). Cookie `grimba_translate_para_X`. Translation badge says "Traduit par NobuAI" | One paragraph translates on click |
| **E7** | **Admin "regenerate summary" button** — on the post edit screen, a button that clears `summary_nobuai` + queues regeneration | Admin click triggers cron pickup within 60 s |

---

## Fleet F — Performance & SEO

**Why:** GrimbaNews lives or dies on Google Discover. Performance + structured
data + sitemap are non-negotiable.

| ID | Sprint | Acceptance |
|----|--------|------------|
| ✅ S214 | Image lazy-load audit — Grimba-facing images now carry loading/decoding hints and dimensions; main homepage/story heroes stay eager | Uncommitted worktree |
| ✅ S215 | Cluster-page query optimization — story source metadata is resolved once in `post.blade.php` and reused by sidebar/article-list partials | Uncommitted worktree |
| ✅ S208 | Sitemap.xml generation — Botble sitemap index extended with Grimba static, sources, story-clusters | Uncommitted worktree |
| ✅ S209 | schema.org JSON-LD — Grimba NewsArticle block uses NobuAI-rendered copy and cluster `mainEntityOfPage` | Uncommitted worktree |
| ✅ S210 | Open Graph polish for /coffre and /local — tailored Grimba OG cards + layout-level image override | Uncommitted worktree |
| ✅ S211 | Preload hint sweep — shared partial preloads generated Fraunces/Public Sans WOFF2 slices when present | Uncommitted worktree |
| ✅ S212 | HTTP-cache audit — public cache headers on homepage, /sources*, /comparatif* with cookie-aware Vary | Uncommitted worktree |
| ✅ S213 | Image CDN proxy — source logos flow through constrained `/img-proxy?u=...` cache for Clearbit/Google favicon assets | Uncommitted worktree |

---

## Fleet G — A11y & I18n

**Why:** French-speaking audiences include disabled readers and screen-reader
users; the cinematic design must not exclude them.

| ID | Sprint | Acceptance |
|----|--------|------------|
| ✅ S216 | Focus-visible audit — global Grimba link/button/form/tab outline coverage with dark-mode color override | Uncommitted worktree |
| ✅ S198 | ARIA pass on bias filter tabs | Uncommitted worktree |
| ✅ S218 | Color contrast check — `--gn-muted` now defined and 60/65% opacity text maps to solid soft ink in both themes | Uncommitted worktree |
| **G4** | **EN locale completeness** — all `__()` strings have an `en.php` translation. Currently most are FR-only with FR fallback | `app()->setLocale('en')` renders /coffre, /pour-vous, /local fully |
| **G5** | **Keyboard navigation** — tab-order audit on every page; trap focus correctly in modals (newsletter, onboarding) | Manual keyboard-only walkthrough passes |
| ✅ S217 | Skip-to-content link — first focusable link on both Grimba layouts jumps to `<main id="grimba-main-content">` | Uncommitted worktree |

---

## Fleet H — Testing & QA

**Why:** GrimbaNews is now ~25 routes + ~20 partials. Without tests, every
sprint risks regression.

| ID | Sprint | Acceptance |
|----|--------|------------|
| **H1** | **PHPUnit: vault routes** — 6 tests covering /coffre empty, /coffre with cookie, /coffre/export.csv empty, /coffre/export.csv with cookie, header badge, save-button toggle endpoint | `vendor/bin/phpunit tests/Feature/VaultTest.php` green |
| **H2** | **PHPUnit: cluster page** — 4 tests: cluster size 1 (legacy fallback), cluster size 2+ (story page), one-sided cluster (callout fires), multi-bias cluster (no callout) | Tests green |
| **H3** | **PHPUnit: extractive synthesis** — given a cluster, asserts each bullet attributes to a unique source, dedupe works, max 5 bullets | Tests green |
| **H4** | **Playwright: golden-path smoke** — record a 5-step flow: home → topic-chip → story page → save → /coffre → unsave. Run in CI nightly | Playwright report green |
| **H5** | **CSP enforcement** — currently report-only? Audit the report-uri logs, fix violations, flip to enforce | Browser console clean on every route |
| **H6** | **Backup / restore drill** — confirm DB + media are nightly-snapshotted to S3 (or equivalent), restore one snapshot to staging | Restored snapshot's homepage renders |
| **H7** | **Load test** — k6 script hammering /coffre, /, /sources at 50 RPS for 5 min. Document p95, p99, error rate | Report committed at `docs/loadtest-YYYY-MM-DD.md` |

---

## Fleet I — Marketing & Growth

**Why:** Vader wants a real readership, not a portfolio piece. These sprints
turn the product into a growth flywheel.

| ID | Sprint | Acceptance |
|----|--------|------------|
| **I1** | **Newsletter double opt-in** — current /newsletter/subscribe is single-step. Send confirmation email via Mailgun/Sendgrid; unconfirmed subs marked `pending`, never emailed | Confirmation link works |
| **I2** | **Cookie consent + analytics** — wire Plausible/Umami (privacy-respecting) gated on consent. NEVER GA. NEVER without consent | Page views land in dashboard after accept |
| **I3** | **Referral kit** — `/parrainer` page with copy-shareable links (`?ref=XYZ`). Track via cookie + `members.referrer_id` column on signup | Member signups carry referrer attribution |
| **I4** | **SEO meta sweep** — every route has unique `<title>` ≤ 60 chars, `<meta description>` ≤ 160, canonical tag. Audit with `screaming-frog` or equivalent | Audit report committed |
| **I5** | **Editorial newsletter draft** — Vader-written weekly digest template (top 5 clusters + most-saved + biggest blindspot). Renders to MJML → HTML | Template at `resources/views/emails/weekly-digest.blade.php` |
| **I6** | **Press kit page** — `/presse` route with logo downloads, fact sheet, recent coverage, contact | Renders, all assets accessible |
| **I7** | **Affiliate / partnership hooks** — `partners.php` config + sidebar widget surfaces 1–2 partner outlets per category | Renders for at least one partner |
| **I8** | **Subscription tier gate** — paid tier (`grimba_full_article_active = true` already exists) — wire Stripe checkout, account upgrade flow, paywall on full-article reader | Checkout completes with test card |

---

## Fleet J — Admin & Editorial Tooling

**Why:** Vader edits posts directly today. The admin surfaces need to make
editorial review of bias / cluster / source classification cheap.

| ID | Sprint | Acceptance |
|----|--------|------------|
| **J1** | **"Cluster review" admin queue** — list of clusters with conflicting bias signals (one-sided + > 5 posts, or 3+ sides + < 2 posts each). Each row is a 1-click "merge" / "split" / "approve" action | Action persists on the cluster |
| **J2** | **Source-classification dashboard** — admin view of all `news_sources` ranked by `credibility_score`, with inline edits for `bias_rating`, `ownership_type`, `owner_name`, `country` | One source can be edited in-place |
| **J3** | **"Coverage map" admin** — heatmap of which clusters have only L vs only R coverage (auto-derived from S181) so editorial knows where to seek out the missing side | Renders a visualization |
| **J4** | **Bulk re-classify** — given a category id, re-run `GrimbaCategoryClassifier::classify` on every post and report changes | CLI command + admin button |
| **J5** | **Vault-events analytics dashboard** (depends on C8) — most-saved posts per week, conversion funnel from save → return visit | Renders for one week of data |
| **J6** | **Source health monitor** — last fetch timestamp + last error per RSS feed; red row when no fresh items in 24h | Renders, identifies broken feeds |

---

## Cross-Cutting Constraints (apply to every sprint)

1. **Steve-styled** — paper bg, ink text, Fraunces titles, glass-panel cards.
   No bare Bootstrap, no admin-style tables on user pages.
2. **NobuAI brand only** — never name a real LLM provider in user-facing text,
   even in error messages. Admin/server logs may name providers.
3. **Cookie-only persistence over auth** when possible — keeps the UX low-friction.
4. **`darkvaderfr` git mandatory** — every commit pushes BEFORE prod. No
   direct VPS edits.
5. **Bias color language is fixed** — `#3b82f6` / `#a8a8a8` / `#e84c3d` / muted.
   Don't introduce new lean colors.
6. **Self-check after every sprint** — render the live URL and verify the
   actual change works, don't trust the diff.
7. **No emoji in code/comments unless the user explicitly asks.**
   Reading-time chip uses `⏱` only because it ships in a user-facing string.

---

## Suggested Execution Order

After Fleet A unblocks (push/deploy):

1. **Fleet B — story page completion** (B1 first; orphan-post layout polish)
2. **Fleet C — vault maturity** (C1 + C5 are highest-leverage)
3. **Fleet F — perf/SEO** (F3 + F4 unlock organic traffic)
4. **Fleet H — testing** (H1 + H2 protect everything else)
5. **Fleet D — discovery & nav** (D1 first; visual parity gap)
6. **Fleet E — NobuAI** (when key + budget land)
7. **Fleet I — marketing**
8. **Fleet G — a11y**
9. **Fleet J — admin tooling**

Each fleet should land in 1–2 sessions; each sprint is sized to commit
independently. Push at the end of each sprint per CLAUDE.md cadence.
