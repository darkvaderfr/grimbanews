# Session 10 polish queue 2 — 1 hour

**Date:** 2026-05-05  
**Lead:** Steve Jobs (CPO)  
**Co-leads:** Alex Morgan (UI/UX), Nina Patel (Lead FE)  
**Cadence:** one commit per sprint, push immediately, no batching.

After the polish trail S319–S335 closed Vader's first round, this queue picks the next 9 surfaces. Focus stays on visible polish + a11y + the few remaining P1 carry-overs from the Ground.news fleet.

---

## S337 — 404 / error page Steve-quality

**Why:** Default Botble error pages are stock and break the brand promise on any wrong URL.

**Plan:**
- New view `views/error-404.blade.php` (or override `errors/404.blade.php`) with the cinematic glass treatment: kicker "PAGE INTROUVABLE", Fraunces title, lede, two CTAs ("Retour à l'accueil" / "Rechercher"), small useful links rail (recent articles).
- Acceptance: hit `/this-does-not-exist` and see the Steve-styled 404 in light + dark.

## S338 — Auth pages Steve-quality (login / register)

**Why:** Botble's stock login/register form looks generic. We have a brand identity to maintain.

**Plan:**
- Override or theme the public login/register routes with a glass-panel form sitting on the paper bg. Preserve the Botble form fields (we don't change auth backend).
- Acceptance: `/login` and `/register` render with Steve chrome in both themes.

## S339 — Search filter UX + empty state

**Why:** `/search` filter row is cramped, mixes input styles. Empty state when no results is generic.

**Plan:**
- Tighten the filter row: search field + 4 select-style filter pills + dates inline at desktop, stacked on mobile.
- Empty state: cinematic "Aucun résultat pour « X »" with two CTAs ("Voir tous les dossiers" / "Réinitialiser la recherche").
- Acceptance: `/search?q=blahblah-no-match` shows the new empty state.

## S340 — Reading-time chip + save-button feedback

**Why:** Reading-time chip already exists from S179 but isn't on every card variant. Save button has no visible feedback when toggled.

**Plan:**
- Audit every card include and ensure reading-time renders when post has a description ≥30 words.
- Save button: when clicked, briefly flash a "Sauvegardé · ★" toast (toast already exists somewhere, reuse).
- Acceptance: every card either has a reading-time chip or shows that the post has none; save click triggers a visible affirmation.

## S341 — Story page right sidebar polish

**Why:** Story page sidebar has 3 panels (coverage-details, bias-distribution, ad-slot, …). The first two could overlap.

**Plan:**
- Audit `partials/story/coverage-details.blade.php` vs `partials/story/bias-distribution.blade.php` for overlap with the new `story-breakdown` panel that S323 compressed.
- If `coverage-details` and `bias-distribution` repeat the breakdown's data, drop the weaker one.
- Acceptance: story page sidebar renders 2 distinct panels max (not 3+ overlapping bias views).

## S342 — Hover state polish on cards

**Why:** Cards mostly have a small translateY hover. We want a slightly more elegant lift + headline color shift to feel cinematic on desktop.

**Plan:**
- Audit `.article-card:hover`, `.grimba-comparison-index__card:hover`, `.grimba-most-read__item:hover` for consistency.
- Apply: 2px translateY, 14% darker shadow, headline gets accent underline animation.
- Acceptance: hover any card on desktop and feel a unified lift.

## S343 — A11y missing aria-labels + alt sweep

**Why:** Icon-only buttons (theme switch, save toggle, share, region picker) may lack aria-labels. Some images may have missing alt.

**Plan:**
- grep public/themes/echo for `<button` with no `aria-label` and `<img` with no `alt`. Add labels.
- Acceptance: spot-check 5 icon buttons → all aria-labelled.

## S344 — Edition-aware bias color flip with footnote (P1 carry-over)

**Why:** Gap analysis flagged this as P1. We deferred deliberately, kept FR convention everywhere. But Ground.news flips per region — and our explainer page footnotes the choice. Worth a small toggle that flips on the `?fr_convention=0` query param OR a setting.

**Plan:**
- Add a `--gn-flip-bias` CSS hook that swaps `--gn-left` and `--gn-right` for users in non-FR regions (default off, behind a setting).
- Update the bias-bar explainer page to mention how to switch.
- Acceptance: explainer page text updated, hook in place.

## S345 — Final sweep + memory close

**Plan:**
- Headless full-page sweep on key routes light + dark.
- Update `project_grimbanews_next_prompt.md`.
- Acceptance: 0 obvious regressions; memory points next session at remaining backlog.

---

## Cadence

- One commit per sprint, push to `darkvaderfr/grimbanews:main` immediately.
- No `git add -A`. Stage by name.
- Co-author trailer required.
- Don't touch `CLAUDE.md` (unrelated local change).
- No migrations.
- If a sprint can't ship cleanly in ~10 min, defer + document why.
