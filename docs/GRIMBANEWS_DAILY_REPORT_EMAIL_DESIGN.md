# GrimbaNews — Daily Report Email Design

**Status:** plan v0
**Owner:** Henry Walker (Editorial) + Olivia Davis (Lifecycle) + Liam Smith (PM)
**Walks:** Mythos S1382 (daily report email) deferred → partial
**Gating dependency:** new `grimba:daily-report-email` command + cluster ranking signal + per-reader cadence opt-in (`members.daily_report_optin`).

## Why this exists

Currently the heaviest digest is weekly (vault digest + saved-search digest). Readers who want a once-a-day editorial snapshot have no path. The daily report is the bridge between breaking-news push (which most readers turn off) and weekly digest (which arrives too late).

## v1 content shape

| Block | Source |
|---|---|
| Hero cluster | Top-ranked cluster from last 24h (S1502 ranker) |
| 3 secondary clusters | Cross-bias-mix selection (one left-skew, one center, one right-skew) |
| Blindspot pick | Top `/angles-morts` article from last 24h |
| Methodology footer | Why this cluster is hero (signals shown) |

No NobuAI free-text in v1 body — body is curated by ranker + bias-balance filter, not generated. Generated NobuAI summary blocks ship in S1384+.

## v1 send shape

- Daily 06:30 reader-local time.
- Per-reader opt-in only (no auto opt-in on signup).
- One-click pause for 7 days.
- One-click hard unsubscribe.

## Surrogate today

- Vault digest (weekly).
- Saved-search digest (weekly).
- `/heure-de-pointe` rail (on-site only).

## Anti-patterns

- No clickbait subject lines. Subject = top-cluster headline + ISO date.
- No automatic AI-rephrased headlines (provider neutrality).
- No mixing ads into v1 body.

## Cross-references

Master plan: S1382. Sister: S1384 (per-topic), S1387/S1588/S1589 (A/B), S1389 (analytics), S1390 (launch playbook), S1281-S1290 (newsletter v2 set). Memory: `feedback_nobuai_model_branding.md`.
