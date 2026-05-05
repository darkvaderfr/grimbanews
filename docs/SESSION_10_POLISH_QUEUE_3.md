# Session 10 polish queue 3 — 1 hour

**Date:** 2026-05-05  
**Lead:** Steve Jobs (CPO)  
**Co-leads:** Alex Morgan (UI/UX), Nina Patel (Lead FE)  
**Cadence:** one commit per sprint, push immediately, no batching.

After queues 1+2 closed Vader's polish round + the P1 carry-overs, this queue picks 9 surfaces still on the long-tail list — pages I haven't audited yet, perf nits, and consistency sweeps.

---

## S346 — /contact page Steve-quality

**Why:** /contact is reachable from the footer + every "contester un classement" link, but never audited. Likely stock Botble form.

**Plan:**
- Override or theme the contact form with the glass-panel + Fraunces title pattern.
- Acceptance: `/contact` matches the about / FAQ visual language.

## S347 — /account (member) page audit

**Why:** Logged-in members land on /account. We have a custom view (S168) but it predates the recent dark-mode passes.

**Plan:**
- Headless dark + light screenshot. Fix any contrast / form-input regression.
- Acceptance: `/account` legible in both themes.

## S348 — Mobile bottom-nav active state

**Why:** The mobile bottom nav (`grimba-mobile-nav`) shows 5 icons. Currently no visual hint about which route the reader is on.

**Plan:**
- Compute current route in the partial; flag the matching `__item` with `is-active` class.
- CSS: active item gets a brighter color + a 2px top accent in the bias-bar's `gn-accent` red.
- Acceptance: visit `/coffre`, the Coffre item in the bottom nav reads as active.

## S349 — Story hero gradient lighter for clear photos

**Why:** The contrast-styles overlay (`rgba(0,0,0,.86)`) was sized for *vivid* photos with dark crops at the bottom. On clear / well-lit hero photos, it crushes the image.

**Plan:**
- Dial the radial-gradient back to ~60% intensity. Headlines stay readable thanks to the existing text-shadow.
- Acceptance: a story with a bright hero photo renders without an oppressive dark veil.

## S350 — OG images for explainer / FAQ / about / methodology

**Why:** New pages from S312–S318 land in social shares with the default site OG image. Each should have a context-specific OG card.

**Plan:**
- Add `Theme::set('grimba_og_image', ...)` per page or wire a per-route OG controller.
- Use the existing `/og/placeholder/{id}.svg` infrastructure to generate SVG-based OG cards for the 4 pages.
- Acceptance: meta `og:image` URL per page matches a route-specific image.

## S351 — Reading-time gating

**Why:** S179 reading-time partial computes minutes from word count. We told S340 it'd skip when description is too short, but the partial logic should be re-verified — and the threshold should produce sensible numbers.

**Plan:**
- Read `partials/reading-time.blade.php`, confirm it bails on `<30` words and produces realistic time at 200wpm.
- Add a tiny "≈" prefix so readers understand it's an estimate, not a hard count.
- Acceptance: chip shows "≈ 2 min" for a 380-word lede; no chip for 12-word stub.

## S352 — Footer link contrast bump in dark mode

**Why:** Footer links in dark mode read at low opacity. Should bump to legible.

**Plan:**
- Inspect `partials/home/footer-dark.blade.php` styles. Bump dark mode anchor color to `var(--gn-ink)` at 0.85 opacity (was 0.5–0.6).
- Acceptance: footer links readable at a glance in dark mode.

## S353 — Sticky desktop nav slight shrink on scroll

**Why:** The header utility bar + main header take ~120px of vertical real estate. On scroll the user scrolls past content needlessly.

**Plan:**
- IntersectionObserver-driven shrink: when the user scrolls past the urgency banner, the main header collapses by ~12px.
- Acceptance: sticky nav still readable, more content visible above the fold on scroll.

## S354 — Final sweep + memory close

**Plan:**
- Headless full-page sweep on every key route + the new ones touched here.
- Update `project_grimbanews_next_prompt.md`.
- Acceptance: 0 unexpected regressions; memory points next session at remaining backlog (img-proxy backend, real factuality data integration).

---

## Cadence rules

- One commit per sprint, push immediately.
- No `git add -A`.
- Co-author trailer required.
- Don't touch `CLAUDE.md`.
- No migrations.
- If a sprint stalls, defer + document.
