# Ground.news — Live Research Brief

**Lead:** Steve Jobs (CPO)  
**Contributors:** Alex Morgan (UI/UX), Nina Patel (Lead FE), Liam Smith (PM)  
**Date captured:** 2026-05-01  
**Method:** WebFetch + WebSearch on public Ground.news pages and help-center articles. Login-walled UI inferred from public docs and Wikipedia. No DOM inspection — design intuition is calibrated against described layouts and methodology pages, not pixel measurements.

This is the **input** for `GROUND_NEWS_GAP_ANALYSIS.md` and `GROUND_FIDELITY_FLEET.md`. Don't ship anything off this doc alone.

---

## 1. The thesis

Ground.news's whole product is **transparent source diversity rendered as data viz**. The bias bar is the hero. Every other surface — story page, blindspot feed, source ranking, daily briefing, newsletters — is either *another way to look at the bar* or *a way to drill into it*. Their typography, color palette, and chrome are deliberately neutral so that the bias signal pops.

GrimbaNews is built on the same thesis. We already have:
- 3-tier coverage bar (`grimba-coverage`)
- Bias chips with the same canonical colors
- Blindspot tag and feed
- Cookie-driven cookie-only follow / theme / region
- Save-for-later vault
- Multi-source story page with extractive synthesis
- Region-aware editorial cuts (Africa / International)

The fidelity gap is **methodological depth + density of data viz**. Ground gives readers 7 bias tiers, 5 factuality tiers, 8 ownership categories. We compress to 3+1. Ground shows the bias bar on every card, in every newsletter, on every source page. We surface it inconsistently. Closing this gap is what this fleet is about.

---

## 2. Page inventory (public surfaces we found)

| Ground page | URL pattern | Purpose |
|-------------|-------------|---------|
| Homepage / Top Stories | `/` | Hero "Daily Briefing" + feed of top-coverage stories |
| Blindspot Feed | `/blindspot` | Stories with imbalanced coverage. Tabs: All / For the Left / For the Right. Paywalled after N reads |
| Story / Coverage page | `/article/{slug}` | Per-story coverage hub: bias bar, summary, full coverage list, source breakdown |
| Source detail | `/source/{slug}` | Per-publisher: bias chip, factuality, ownership, country, recent stories |
| Source rankings | `/sources` | Filterable ranked list of all tracked publishers |
| Topic / Interest | `/interest/{slug}` | Topic hub (e.g. /interest/world) — feed + coverage stats for the topic |
| Local | `/local` | Region-filtered feed |
| My News / For You | `/my` | Logged-in personalized feed |
| My News Bias / Bias Vantage | `/my-news-bias-vantage` | Reader's own reading-history bias breakdown |
| Daily Briefing | linked from hero | Daily editor-curated digest |
| Newsletters | `/newsletters/blindspot-report/Apr-14-2026` etc | Daily Ground, Blindspot Report, Burst Your Bubble |
| Bias Bar explainer | `/bias-bar` | Marketing/methodology page teaching the bar |
| Rating System | `/rating-system` | Methodology |
| Media Bias | `/media-bias` | Editorial position on bias |
| About / FAQ | `/about` `/frequently-asked-questions` | Brand + product summary |

GrimbaNews already covers: home, blindspot feed (`/angles-morts`), story page (`/article/*`), sources (`/sources`), local, "Pour vous", coffre (vault). We're missing: source detail with full bias/factuality/ownership panel, topic hubs as first-class pages, bias vantage / reading-history breakdown, rating-system explainer, bias-bar explainer page.

---

## 3. The bias bar (the hero element)

### Visual spec, US edition

- 3 segments laid out left → center → right in that fixed order
- Colors: **blue** for left, **neutral white/grey** for center, **red** for right
- US convention only; non-US editions **flip blue and red** (red=left, blue=right) to match local political-color conventions
- Segment widths = % of bias-rated sources reporting on the story
- Hard-edged stacked bar (not gradient) per the help docs
- Labels: "L 23% | Center 59% | R 18%" or "59% Center coverage: 125 sources"
- Below or beside the bar: source count
- Click-through: tapping a segment filters the source list to that side

### How it's surfaced
- On every feed card (compact form)
- On every story page header (full form, with the comparison summary tab below)
- On every newsletter blindspot entry
- On the source ranking page (per-source bias chip = a single 1-segment bar of that source's color)

### Edge cases (what their docs don't say but we'll need)
- 1-source story: render the bar as a single full-width segment in that source's bias color (don't fake percentages from a sample of 1)
- All-one-side (0% other side): segment renders at 100% of that side; the missing side is implicit visual proof of the blindspot
- Equal split (33/33/33): render normally, no special treatment
- Far-Left / Far-Right sub-tier: 7-tier classification on the **source** (chip uses 7 colors); the **story bar** compresses to 3 sides

### Our current state
We already have `partials/coverage-bar.blade.php` doing 3-tier blue/grey/red. Color tokens (`--gn-left: #3b82f6`, `--gn-center: #a8a8a8`, `--gn-right: #e84c3d`) match Ground's intent. The 7-tier extension is what we don't ship yet. **Edition-aware color flip is something Ground does that we should adopt** — but only on a per-edition basis (currently we render the same FR-perspective colors regardless of region).

---

## 4. The 7-tier bias classification (per source)

| Tier | Description (Ground methodology, paraphrased) | Suggested chip color |
|------|----------------------------------------------|----------------------|
| Far Left | Extreme left, loaded language, may omit anti-left info | `#1d4ed8` (deep blue) |
| Left | Moderate left bias, party-leadership-aligned | `#3b82f6` (our `--gn-left`) |
| Lean Left | Slight liberal bias, otherwise factual | `#7aa6f9` (faded blue) |
| Center | No discernible position, well-sourced | `#a8a8a8` (our `--gn-center`) |
| Lean Right | Slight conservative bias, otherwise factual | `#f49b94` (faded red) |
| Right | Moderate right bias, party-leadership-aligned | `#e84c3d` (our `--gn-right`) |
| Far Right | Extreme right, loaded/stereotype language, may omit anti-right info | `#b91c1c` (deep red) |

**Aggregation:** average of AllSides, Ad Fontes Media, Media Bias Fact Check.

**Where it shows:**
- Source detail page header (the chip is the full 7-tier color)
- In the full-coverage list under a story, each row's source has its 7-tier chip
- On a source's recent-stories list

The story-level bar always compresses to 3 sides for readability — only sources get the 7-tier resolution.

---

## 5. Factuality (5 tiers)

| Tier | Definition | Suggested chip color / icon |
|------|-----------|----------------------------|
| Very Low | Sensational, distorting, unreliable | dark red, `⚠` |
| Low | Significant accuracy issues, lacks credible sourcing | red, `!` |
| Mixed | Blend of objective and opinion; failed multiple fact-checks | amber/orange, `~` |
| High | Mostly fact-based, balanced, rapid corrections | green, `✓` |
| Very High | Very reliable, well-researched, minimal bias | deep green, `✓✓` |

**Aggregation:** average of Ad Fontes + Media Bias Fact Check (only 2 sources, AllSides doesn't rate factuality the same way).

**Where it shows:** source detail page, full-coverage list per row, source ranking page, paywall indicator (separate `$` icon).

We already have a `credibility_score` integer on `news_sources`. The 5-tier mapping is straightforward: 0–20 Very Low, 21–40 Low, 41–60 Mixed, 61–80 High, 81–100 Very High. Front-end derives label + chip from the score.

---

## 6. Ownership (8 categories, hand-coded)

| Category | One-line | Visualization |
|---------|---------|---------------|
| Media Conglomerates | Multi-outlet roll-ups via M&A | grey building icon |
| Private Equity | Cost-cutting fund-controlled | grey $ icon |
| Individual | Wealthy individuals (Bezos, Musk, etc.) | grey person icon |
| Government | State-funded, may be independent or state-directed | tan flag icon |
| Telecom | Connectivity company vertical integration | grey wifi icon |
| Corporation | Tech / non-PE corporate ownership | grey factory icon |
| Independent | No >5% government or corp stakes | green leaf icon |
| Other | Unclassifiable | grey question mark |

Ground says they hand-coded 2,276+ outlets. We can seed a smaller franco-focused set (TF1, Le Monde, Mediapart, RFI, BFM, Jeune Afrique, etc.) and grow.

**Where it shows:** source detail page, optional chip on full-coverage rows, "Owned by:" line on the story page.

---

## 7. Story / Coverage page (the most important page)

What Ground shows on a clicked-into story:

1. **Headline + lede** (standard editorial)
2. **Bias bar** (full-width, prominent, with source count)
3. **Bias Comparison Summary tab** — AI-generated, three columns/tabs (Left framing / Center framing / Right framing) with paraphrased framings of how each side is reporting the story. Disclosure: "We use AI to help us analyze content at the speed and volume our readers expect. Human-in-the-loop oversight."
4. **Ground Summary** — "key points from all of the articles reporting on a story, from left, right, and center" (extractive, not generative)
5. **Full Coverage carousel / list** — every source covering the story:
   - logo, name
   - bias chip (7-tier color)
   - factuality chip (5-tier color)
   - ownership chip
   - paywall `$` icon
   - country flag
   - the source's actual headline (so the reader sees framing differences in real)
   - timestamp
   - external link
6. **Coverage Timeline** — chronology of when each source published (vertical or horizontal)
7. **Compare action** — pick 2–3 sources, see headlines side-by-side
8. **Save / Share / Follow** (vault, share sheet, follow source)

We already have:
- ✓ headline + lede
- ✓ bias bar (S171 dark-mode coverage)
- ✓ multi-source extractive synthesis (S175)
- ✓ story timeline (S180)
- ✓ unbalanced-coverage callout (S181)
- ✓ save-for-later vault (S173)
- ✓ bias-color dots in synthesis (S183)

We're missing:
- ✗ Bias Comparison Summary (AI 3-column framing)
- ✗ Full Coverage list with per-source bias / factuality / ownership / paywall / country
- ✗ Compare action (pick N sources, side-by-side headline diff)
- ✗ Source detail page reachable from coverage rows

---

## 8. Source detail page

Ground's source page (we couldn't fetch one cleanly but the help docs describe it):

- Header: source logo + name + country flag
- Three big chips: 7-tier bias / 5-tier factuality / ownership category
- "Owned by: {parent company}, classified as {category}"
- Stories tracked count + coverage timeline sparkline
- Recent stories grid (using normal feed cards)
- "Sources with similar bias" rail
- Methodology footer: "Bias rating averaged from AllSides + Ad Fontes + MBFC"

We have `/sources` (rankings) but no per-source detail page. This is a P1 gap.

---

## 9. Topic / Interest pages

- Header: topic name + description + follow button + story/source counts
- Coverage breakdown bar **for the topic as a whole** (e.g. "Coverage on AI: 41% Left / 32% Center / 27% Right")
- Feed of top stories on the topic (normal cards, but filtered)
- Sidebar: "Top sources for this topic" + "Topic-specific blindspots" + "Related interests"

We have category routing. We don't have the topic-aggregate coverage bar, the topic-blindspots filter, or the related-interests rail.

---

## 10. Newsletter format (Blindspot Report)

Each entry:
1. Section header — "For the Right" / "For the Left"
2. Image + headline
3. Blindspot badge with coverage % gap
4. Source count ("16 sources")
5. 2–3 sentence summary
6. Read More link
7. Mini bias bar

Editorial voice: data-driven, no advocacy. Example phrasing: "France is speeding up electrification as the Iran war keeps oil and gas prices high…" — no "Look at how the right is ignoring this!" framing. Just facts.

---

## 11. Chrome / typography / color

Inferred from the homepage:
- **Font:** sans-serif throughout (no serif display font like ours) — but this is the part where Steve says "they're wrong, our serif gives us editorial gravitas Ground doesn't have." We keep Fraunces for headlines.
- **Color palette:** near-white background, charcoal text, generous whitespace, muted grey dividers
- **Bias colors:** the only saturated color in the entire UI

Steve's call: **don't reskin to Ground's neutral**. Our paper-and-ink + glass-panel cinematic is a stronger differentiator than copying their look. We adopt their **information rigor**, not their typography.

---

## 12. UX patterns to import (Steve's pick)

In priority order:

1. **Edition-aware bias colors.** When the user is on an Africa or International edition, the bar still uses our blue=left, red=right convention, but we add a tiny "FR convention" / "Convention francophone" footnote on the bias-bar explainer page so readers know we're not silently flipping.
2. **Per-source 7-tier bias chip** in the full-coverage list (replace the current 3-tier compressed chip on `/article/*`).
3. **5-tier factuality chip** rendered alongside bias on coverage rows.
4. **8-category ownership chip** rendered next to the source name.
5. **Paywall indicator** (`$`) as a tiny grey icon.
6. **Compare action** — select 2–3 sources from a story's coverage list, see their headlines side-by-side in a modal.
7. **Source detail page** at `/sources/{slug}` with the full source profile.
8. **Topic-aggregate bias bar** on category and search-result pages.
9. **Bias Comparison Summary tab** under each story headline (we have extractive synthesis already, this is a 3-column extension that splits left / center / right framings).
10. **Bias bar explainer page** at `/comprendre-le-baromètre` (FR), explaining methodology, edition convention, and edge cases.

---

## 13. What we deliberately *don't* import

- Sans-serif throughout — keeps us looking like every other Substack-era outlet
- Their "US Edition" flag pattern in the top-right — we already have an `Afrique / International` toggle there
- Their paywall on the blindspot feed — we don't paywall blindspots, we paywall full-article reading (S749 + Stripe wiring)
- The "My News Bias Vantage" reading-history-as-bias-pie — interesting but Phase 2; cookie-only follow + region is enough for Phase 1
- The browser extension — Phase 3
- The mobile app — separate Capacitor/native sprint, not a front-end web sprint

---

## 14. Sources (research provenance)

- [Ground News homepage](https://ground.news/)
- [Ground News About](https://ground.news/about)
- [Ground News Blindspot](https://ground.news/blindspot)
- [Bias Bar explainer](https://ground.news/bias-bar)
- [Rating System](https://ground.news/rating-system)
- [Media Bias position](https://ground.news/media-bias)
- [How to read the Bias Bar](https://help.ground.news/en/articles/245249)
- [Bias Comparison Summary feature](https://help.ground.news/en/articles/3189505)
- [Find Coverage / search UX](https://help.ground.news/en/articles/5609857)
- [How Ground News helps readers see news differently](https://help.ground.news/en/articles/485057)
- [Frequently Asked Questions](https://ground.news/frequently-asked-questions)
- [Blindspot Report Apr-14-2026 newsletter](https://ground.news/newsletters/blindspot-report/Apr-14-2026)
- [Wikipedia: Ground News](https://en.wikipedia.org/wiki/Ground_News)
