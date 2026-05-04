# Session 10 polish queue — 1 hour

**Date:** 2026-05-05  
**Lead:** Steve Jobs (CPO)  
**Co-leads:** Alex Morgan (UI/UX), Nina Patel (Lead FE)  
**Cadence:** one commit per sprint, push immediately, no batching.

After Vader's visual review caught issues the audit panel missed, the polish trail S319–S327 closed the obvious bugs. This queue picks off the next layer — things that would be the next "good but I see issues" round.

Priority: visible-impact first, then a11y/perf, then close-out.

---

## S328 — Daily Briefing hero card on home

**Why:** Ground.news's homepage opens with a "Daily Briefing" hero ("X stories · Y articles · Zm read"). Our home opens with the all-sides rail directly. The gap analysis flagged Daily Briefing as P1 — still untouched.

**Plan:**
- Add a `partials/home/daily-briefing.blade.php` that renders today's top story (highest cluster source-count + most-recent) as a cinematic 21:9 hero card with kicker, headline, "X sources · Ym read" metadata, and a coverage bar.
- Insert at top of `grimba-home.blade.php`'s main, above the existing all-sides-rail.
- Acceptance: home shows a dedicated Daily Briefing card before the rail.

## S329 — Editorial-placeholder swap on remaining direct RvMedia callers

**Why:** S326 fixed `post-hero-img.blade.php`, but `story-comparison.blade.php` and `partials/home/all-sides-rail.blade.php` still call `RvMedia::image` / `RvMedia::getImageUrl` directly. Posts with broken `image` columns there still render the 1920×1080 dimension box.

**Plan:**
- In each direct-call site, pre-resolve via `getImageUrl()` and compare against `getDefaultImage()`. Match → fall through to `/og/placeholder/{id}.svg` (with `?theme=` propagated).
- Acceptance: grep shows zero remaining unguarded RvMedia::image calls in public partials. Live verify on a story with a broken image URL.

## S330 — Coverage-bar N+1 fix (Zen audit carry-over)

**Why:** Every card render in `partials/home/coverage-bar.blade.php` runs `Post::query()->where('story_cluster_id', $id)->get()`. On `/blog/{topic}` with 24 cards that's 24 cluster fetches on top of the main query. Invisible to users but server-time waste at scale.

**Plan:**
- Add an optional `clusterCounts` parameter to `coverage-bar.blade.php`. When provided, look up counts in O(1) instead of querying.
- Update the loop-level partials (`partials/blog/posts.blade.php` or wherever card iteration lives) to fetch counts once via a single `GROUP BY` and pass the array down.
- Fall back to the current per-card query when `clusterCounts` is absent (preserves callers we miss).
- Acceptance: `php artisan tinker` confirms a 24-card render now runs 1 cluster-count query, not 24.

## S331 — Compare modal focus trap (Zen audit carry-over)

**Why:** The S307 compare modal has Escape-to-close and aria-modal but no focus trap. Tab and Shift+Tab leak into the page behind the backdrop.

**Plan:**
- Add a `keydown` handler that intercepts Tab/Shift+Tab when the modal is open and cycles focus within the modal's focusable descendants.
- Reuse the focus-trap pattern from `partials/home/newsletter-modal.blade.php` (which already does it correctly) so we have one source of truth.
- Acceptance: with modal open, Tab cycles through close button → cards → close button. Shift+Tab reverses. Mouse outside the modal is still receivable (backdrop click closes).

## S332 — Newsletter + onboarding modal dark-mode polish

**Why:** Both modals use `glass-panel` chrome which our S320 fix touched, but the inputs inside (the email field, the "S'abonner" CTA, the topic chips) haven't been re-checked in dark mode after S322's form-input rules landed.

**Plan:**
- Headless screenshot both modals open in dark mode (newsletter via `data-grimba-newsletter-open`, onboarding via `?onboarding=1`).
- Fix any contrast or color regression noted on the captured screenshots.
- Acceptance: both modals look Steve-quality in dark + light at desktop and mobile.

## S333 — Mobile bottom nav + vault FAB dark-mode pass

**Why:** The mobile bottom nav (`grimba-mobile-nav`) and the vault FAB (`grimba-vault-fab`) have dedicated dark-mode rules in grimba-home.css (lines 2185–2200) but were written before our paper/ink tokens stabilized. Worth a one-screenshot QA.

**Plan:**
- Mobile dark screenshot of `/`, `/coffre`, `/pour-vous` focused on the bottom nav + FAB area.
- Fix any contrast or icon-color regression.
- Acceptance: bottom nav icons + labels readable, FAB visible without overpowering.

## S334 — Cookie consent + auto-translate banner dark contrast

**Why:** The cookie consent banner (`partials/cookie-consent.blade.php`) and the "Articles non francophones … traduction NobuAI" banner are both fixed-position elements that could leak light-mode chrome onto dark pages.

**Plan:**
- Inspect the cookie banner with `grimba_cookie_consent` cookie absent (forces it open) on a dark page.
- Inspect the auto-translate banner with `grimba_lang=en` cookie on a French story.
- Fix any cream-on-dark or low-contrast regression.
- Acceptance: both banners are readable in both themes without jarring color contrasts.

## S335 — Skip-link + scroll-to-top + utility-bar polish

**Why:** Accessibility-skip-link `.grimba-skip-link` and any scroll-to-top widget are usually overlooked. Utility-bar (top edition picker / theme switch) is dense and may have contrast issues at small sizes.

**Plan:**
- Tab-key from page load to surface the skip link in light + dark.
- Inspect the utility bar at 320px viewport for overlap / unreadable text.
- Acceptance: skip link visible on focus in both themes; utility bar readable at iPhone SE width.

## S336 — Final sweep + memory update

**Why:** Close-out.

**Plan:**
- Headless full-page sweep on every key route in light + dark, mobile + desktop.
- Update `project_grimbanews_next_prompt.md` with the closing state of the polish trail.
- Acceptance: 0 obvious visible regressions; memory file points the next session at remaining backlog (img-proxy backend, coverage-bar N+1 upgrades, edition-aware color flip).

---

## Cadence

- Each sprint: implement → headless screenshot or curl verify → commit → push to `darkvaderfr/grimbanews:main` immediately.
- No batching, no `git add -A`.
- Co-author trailer required.
- Don't touch `CLAUDE.md` (unrelated local change).
- Don't run migrations.
- If a sprint can't ship cleanly in ~10 min, defer to next session and document why.
