# GrimbaNews — Chinese (ZH) Locale Catalog Plan

**Status:** design v0 (no `lang/zh.json`; ZH detector path not shipped)
**Owner:** Liam Smith (PM) + Nina Patel (Lead Frontend) + Michael O'Connor (Technical Writer)
**Walks:** Mythos S1134 (ZH site UI catalog) deferred → partial
**Gating dependency:** ZH-native editorial reviewer; Simplified vs Traditional split decision.

## Why this exists

ZH is two locales, not one. Simplified (zh-CN) and Traditional (zh-TW / zh-HK) are mutually intelligible but visually distinct. This doc covers both.

## Catalog scope

Two catalogs, ~1,200 keys each:

- `lang/zh_CN.json` — Simplified (mainland + global ZH diaspora)
- `lang/zh_TW.json` — Traditional (Taiwan + Hong Kong + diaspora preference)

Launch sequence: zh_CN first (larger audience), zh_TW second (Vader call on timing).

## Editorial categories (zh-CN baseline)

政治, 经济, 社会, 国际, 非洲, 科技, 健康, 科学, 文化, 体育, 环境, 司法, 教育, 移民

Traditional variant: 政治, 經濟, 社會, 國際, 非洲, 科技, 健康, 科學, 文化, 體育, 環境, 司法, 教育, 移民

## Editorial / political sensitivity

ZH coverage of certain topics (Tiananmen, Tibet, Xinjiang, Hong Kong) is politically sensitive. Editorial policy decision needed before ZH catalog ship:
- Surface source coverage in ZH neutrally with full bias bar
- Document choice in `/methodology` page ZH version
- Be prepared for Great Firewall blocking in mainland — site may serve overseas ZH audience primarily

This is an operator-side call requiring Lucy + Vader sign-off.

## CJK font fallback

Noto Sans SC (Simplified) and Noto Sans TC (Traditional) — separate fallback chains.

## Acceptance gates

Same as ES + CJK font check + zh_CN/zh_TW URL pattern verification.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1134 row)
- Sister docs: `docs/GRIMBANEWS_JA_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_KO_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`
- Existing infrastructure: `lang/fr.json`, `App\Support\GrimbaLanguageDetector`
