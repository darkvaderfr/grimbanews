# GrimbaNews — Multi-Language Launch Ops Playbook

**Status:** playbook v0 (gates on ≥1 non-FR/EN catalog shipped first)
**Owner:** Liam Smith (PM) on coordination + Sara Kim (QA) on smoke + Lucy Leai (CEO) on editorial sign-off
**Walks:** Mythos S1140 (multi-language launch ops) deferred → partial
**Gating dependency:** First non-FR/EN catalog shipped. Per-locale ops cadence requires repeating the per-locale launch checklist.

## Why this exists

S1140 honest-deferred as "needs ≥1 non-FR/EN catalog shipped first." That's true at execution time. The **playbook itself** — the repeatable ops template for every subsequent locale — is operator-side and can be authored now.

## Generic locale launch template

For each new locale `{loc}` (e.g. `es`, `pt-BR`, `de`, `it`, `ar`, `ja`, `zh-CN`, `ko`, `ru`, `he`, `hi`, `sw`):

### Phase 1: Catalog (week 1-2)

1. Copy `lang/fr.json` → `lang/{loc}.json`
2. LLM-assist draft pass (NobuAI-internal, no provider name)
3. Native-reviewer editorial pass
4. Operator review + merge

### Phase 2: Routing wiring (1 day)

1. Add `{loc}` to `GrimbaLocaleEnforce::PRIMARY_LOCALES`
2. Smoke `/{loc}` on staging
3. Smoke language switcher round-trip
4. Verify URL prefix + hreflang emission

### Phase 3: Editorial pages (1 week)

1. Map editorial categories (see per-locale `*_EDITORIAL_PAGES_SCOPE.md`)
2. Per-category copy review
3. Smoke per-category route on staging

### Phase 4: SEO / sitemap (1 day)

1. Regenerate sitemap with `{loc}` URLs
2. Submit sitemap to Google Search Console + Bing Webmaster Tools
3. Verify hreflang tags emitting per page (`/`, `/dossier/{id}`, `/blog/{slug}`)

### Phase 5: Comms (1 week)

1. Press list per locale (operator-side; see per-locale launch checklist)
2. Social posts in target locale
3. Newsletter announcement (FR + EN canonical announcement) + per-locale variant

### Phase 6: Smoke + monitor (1 week)

1. Playwright smoke for `/{loc}/*` routes
2. Lighthouse SEO + Performance + A11y >= 90 each
3. axe-core scan = zero violations
4. Monitor `grimba_automation_runs` for new errors
5. Monitor Plausible / GA for per-locale traffic split

## Per-cadence (one locale per quarter)

Realistic operator-side cadence assuming 1 native reviewer per locale + 1 engineer + 1 PM:
- Q1 post-launch: ES + PT-BR (Romance-pair, share many implementation patterns)
- Q2 post-launch: DE + IT
- Q3 post-launch: AR (RTL deliverable + HE preview) + first CJK (JA)
- Q4 post-launch: ZH + KO + RU + HE + HI + SW (operator picks two based on demand signal)

## Cross-cutting deliverables

- **RTL chrome** (per `docs/GRIMBANEWS_RTL_SUPPORT_PLAN.md`) — must ship before any RTL locale
- **CJK font fallback** — verify before any CJK locale ships
- **Devanagari font fallback** — verify before HI ships
- **Per-locale ad consent rules** (per `docs/GRIMBANEWS_PER_LOCALE_AD_CONSENT_RULES.md`)
- **Per-locale subscription pricing** (per `docs/GRIMBANEWS_PER_LOCALE_SUBSCRIPTION_PRICING_DESIGN.md`)
- **Per-locale launch comms** (per `docs/GRIMBANEWS_PER_LOCALE_LAUNCH_COMMS_PLAN.md`)

## Risks + escalations

- **Editorial reviewer drop-out** — escalate to closest real role (Lucy + Liam fall back to LLM-only with operator final review; never ship LLM-only to readers without operator review).
- **Native typography breakage** — escalate to Nina + Steve for token / chrome adjustment.
- **Translation drift over time** — translation memory (TM) bank + monthly recheck cadence (operator-side).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1140 row)
- Sister docs: `docs/GRIMBANEWS_ES_LAUNCH_READINESS_CHECKLIST.md`, `docs/GRIMBANEWS_PT_BR_LAUNCH_READINESS_CHECKLIST.md`, `docs/GRIMBANEWS_DE_LAUNCH_READINESS_CHECKLIST.md`, `docs/GRIMBANEWS_RTL_SUPPORT_PLAN.md`
- Existing infrastructure: `lang/fr.json`, `lang/en.json`, `App\Support\GrimbaLanguageDetector`, `App\Http\Middleware\GrimbaLocaleEnforce`
