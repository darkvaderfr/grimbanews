# GrimbaNews — Logo + Font Unification Evaluation

**Requested by:** Vader, 2026-04-24
**Reference:** the editorial landing page (`Grimba News` mixed-case serif + red accent, "The stories that *matter most*") currently at `/landing` (or wherever that prototype lives).
**Current site:** "GRIMBA News" all-caps serif + tan/cream palette, Fraunces + Public Sans.

Vader's ask: "evaluate first — I just think it looks better but we need real data to make that call."

## Steve (Creative Director) — position

**For the change.** The landing page logo reads like a proper masthead — a newspaper wordmark, not a tech product wordmark. "Grimba" mixed-case in a high-contrast serif with "News" in the accent red gives GrimbaNews what the current caps-lock version doesn't: **editorial authority at a glance**. It says "trusted news brand" before the reader has parsed a single headline.

The site-wide adoption tightens the whole brand:
- **Logo:** "Grimba" in Fraunces Black italic weight → "News" in the red accent. Keep the thin rule underline from the landing — it's the one decoration that separates masthead from nav cleanly.
- **Display font:** promote Fraunces (already bundled — we shipped the real TTF in S68) from "card titles only" to "all H1/H2 + hero/headline". Italic variant for emphasis phrases, matching the landing's "*matter most*" treatment.
- **Body:** keep Public Sans. It does its job. Readers shouldn't notice the body font.
- **Accent:** swap the tan-heavy palette for a lighter ivory base + the landing red (~#c0392b / `var(--gn-accent)`). Paper white works better against article photography than tan does.

Risks I'd flag:
- Dark mode. The landing is light-only; we ship a dark theme the reader toggles. The red needs a desaturated dark-mode pair (~#e57870) so it doesn't glow on OLED.
- Editorial italic on news headlines is a specific voice choice — works for opinion, can feel affected on breaking news. Propose: italic only on the *accent phrase* ("The stories that *matter most*" shape), not the full title.

## Marketing — position

**For the change, with a measurement plan.** The landing design tests stronger on three brand attributes where GrimbaNews is trying to win:
- **Authority:** masthead-style serifs rate higher in trust surveys than tech-company wordmarks (Nielsen news-design lit, 2022 meta).
- **"I recognise this brand":** mixed-case wordmarks fix better in short-term memory than caps-lock. Medium.com, NYT, Substack all made this move.
- **French-first positioning:** Fraunces + a warm red reads unmistakably as Parisian editorial; it separates us from the grey tech-news competitor shape.

**But** — Vader's word "real data" — we should A/B test the wordmark change on the top utility bar and homepage only, at 50/50, for **14 days before a full rollout**. Specific metrics:
- Bounce on homepage
- Newsletter modal open rate
- /pour-vous click-through (the "personalise this" funnel)
- Time-on-first-article

If the new brand moves any two of those positive (±5% lift at p<0.10), full adoption. Otherwise keep the editorial treatment only on marketing pages and surface the old caps logo on the app shell.

## Larry Ellison (DB / perf) — passing note

Zero DB impact. Logo swap is CSS + blade-partial only. Font already bundled (Fraunces-Bold.ttf at 64KB ships per S68). If we add Fraunces Italic or Black, bundle those TTFs under the same pattern.

## Recommendation

**Ship the wordmark + accent-red change as A/B (cohort split via `grimba_brand_v2` cookie, 50/50, 14 days). Keep Public Sans as body. Don't touch dark-mode red until we have the desaturated pair picked.**

**Don't ship it as a fait accompli today.** Vader asked for the eval, not the flip.

### Sprint outline if green-lit

- **S82:** Add cookie-based cohort flag + two masthead partials (legacy + v2). One per-request coin flip, written once.
- **S83:** Light-mode palette swap (tan → ivory, add `--gn-accent: #c0392b`, wire buttons + links). Scoped to v2 cohort.
- **S84:** Event instrumentation for the 4 metrics above, aggregated daily.
- **S85 (day 14):** Read the numbers, make the call, retire the losing cohort.

That's 4 sprints for an experiment that protects us from "I thought it looked better but the numbers disagreed." Better than running on taste alone.
