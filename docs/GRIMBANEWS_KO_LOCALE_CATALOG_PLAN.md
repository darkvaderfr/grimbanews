# GrimbaNews — Korean (KO) Locale Catalog Plan

**Status:** design v0 (no `lang/ko.json`; KO detector path not shipped)
**Owner:** Liam Smith (PM) + Nina Patel (Lead Frontend) + Michael O'Connor (Technical Writer)
**Walks:** Mythos S1135 (KO site UI catalog) deferred → partial
**Gating dependency:** KO-native editorial reviewer; CJK typography pass.

## Catalog scope

Mirror of ES Tier 1-4 (~1,200 keys). KO specifics:

- **Hangul-only baseline** (no Hanja); matches modern KR press.
- **Honorific register**: 합쇼체 (formal) for chrome; 해요체 (polite) for marketing.
- **Editorial categories**: 정치, 경제, 사회, 국제, 아프리카, 기술, 건강, 과학, 문화, 스포츠, 환경, 사법, 교육, 이민
- **Font fallback**: Noto Sans KR baseline.

## KO editorial relevance

Niche but high-trust market. Korean readers strongly value source provenance — bias bar + factuality score align with KR press-literacy expectations.

## Acceptance gates

Same as ES + CJK font check.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1135 row)
- Sister docs: `docs/GRIMBANEWS_JA_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_ZH_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`
- Existing infrastructure: `lang/fr.json`, `App\Support\GrimbaLanguageDetector`
