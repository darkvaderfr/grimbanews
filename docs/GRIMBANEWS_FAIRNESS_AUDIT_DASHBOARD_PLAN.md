# GrimbaNews — Fairness Audit Dashboard Plan

**Status:** plan v0
**Owner:** David Chen (Data) + Sara Chen (CISO) + Maya Patel (Compliance)
**Walks:** Mythos S1539 (fairness audit dashboard) deferred → partial
**Gating dependency:** an ML feed or personalization layer to audit (S1501+) + opt-in user behavior signals (S1538).

## Why this exists

Without a fairness dashboard, any future ML personalization layer is operating blind on questions like: does recommendation amplify majority-bias content? Does it suppress small-circulation sources? Are minority-locale clusters under-served?

## v1 metric set (when applicable model exists)

| Metric | Source |
|---|---|
| Bias-distribution served vs source bias-distribution | per-source recommendation count / corpus count |
| Source-diversity (Gini coefficient over served sources) | served per-source counts |
| Locale parity | FR/EN/other locale served-rate vs supply-rate |
| Topic parity | per-topic served-rate vs supply-rate |
| Per-reader bias-narrowing trend | week-over-week bias-mix of recommendations |
| Under-served sources | sources with served-rate < expected by Z standard deviations |

## v1 design

- Admin-only dashboard at `/admin/grimba/fairness`.
- Re-computed nightly.
- Per-metric alert thresholds; Slack ping if breach.
- Quarterly summary written into annual transparency report (S2001).

## Anti-patterns

- No fairness metric ever surfaced to advertisers.
- No editorial team override that targets a specific outlet's served-rate without documented rationale.
- No fairness-washing (a single Gini chart is not a fairness audit).

## Cross-references

Master plan: S1539. Sister: S1538 (echo-chamber), S1540 (launch retro), S1501+ (ML feed set), S2001 (transparency report).
