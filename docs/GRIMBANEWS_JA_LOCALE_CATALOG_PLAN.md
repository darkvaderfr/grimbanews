# GrimbaNews — Japanese (JA) Locale Catalog Plan

**Status:** design v0 (no `lang/ja.json`; JA detector path not shipped)
**Owner:** Liam Smith (PM) + Nina Patel (Lead Frontend) + Michael O'Connor (Technical Writer)
**Walks:** Mythos S1133 (JA site UI catalog) deferred → partial
**Gating dependency:** JA-native editorial reviewer; CJK typography pass on chrome.

## Catalog scope

Mirror of ES Tier 1-4 (~1,200 keys). JA specifics:

- **CJK typography**: line-height adjustments, no italic equivalent (use brackets or `text-emphasis`), no underline-as-link (use color + dotted-underline).
- **Honorific register**: catalog ships polite (です/ます) baseline — matches Asahi / Yomiuri register; casual (だ/である) reserved for marketing if Vader approves.
- **Editorial categories**: 政治, 経済, 社会, 国際, アフリカ, テクノロジー, 健康, 科学, 文化, スポーツ, 環境, 司法, 教育, 移民
- **Vertical text (tategaki)** explicitly NOT in scope — modern Japanese web overwhelmingly horizontal.

## CJK font fallback

Already in font-loader (Noto Sans JP, Hiragino Sans). Verify on JA launch:
- No glyph-missing tofu boxes on chrome
- Webfont weights 400 / 500 / 700 all load

## JA editorial relevance

Niche but high-trust audience — Japanese readers value source credibility scoring. Source-roster expansion for JA-language sources gates separately.

## Acceptance gates

Same as ES + CJK font check.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1133 row)
- Sister docs: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_ZH_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_KO_LOCALE_CATALOG_PLAN.md`
- Existing infrastructure: `lang/fr.json`, `App\Support\GrimbaLanguageDetector`
