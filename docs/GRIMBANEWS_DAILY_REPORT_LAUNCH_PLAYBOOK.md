# GrimbaNews — Daily Report Launch Playbook

**Status:** plan v0
**Owner:** Liam Smith (PM) + Henry Walker (Editorial) + Olivia Davis (Lifecycle)
**Walks:** Mythos S1390 (daily report launch playbook) deferred → partial
**Gating dependency:** S1381-S1389 (all daily-report sub-sprints).

## T-minus checklist

| Phase | Step | Owner |
|---|---|---|
| T-21d | Hero-cluster ranker (S1502) validated against 30 days of historical traffic | David Chen |
| T-14d | Template QA across major mail clients (Gmail, Apple Mail, Outlook 365, Yahoo, ProtonMail) | Sara Kim |
| T-14d | Subject-line library drafted (per-locale FR + EN) | Henry Walker |
| T-10d | DKIM/DMARC re-verified on send domain | Jacob Lee |
| T-7d | Beta cohort (200 opt-in readers from weekly-vault-digest list) | Liam |
| T-7d | Methodology page updated | Michael O'Connor |
| T-5d | Analytics dashboard (S1389) live on staging | David Chen |
| T-3d | Send-cap throttling validated (no more than X/sec to avoid IP-reputation hit) | Jacob Lee |
| T-1d | Final dry-run send to internal list | Liam |
| T-0 | Soft launch (opt-in only, no in-product promo) | Liam |
| T+7 | Day-7 review | Liam / David Chen |
| T+14 | In-product promo opens (banner on /coffre) | Steve / Alex Morgan |
| T+30 | Retrospective + S1384 per-topic ship-decision | Liam |

## Success criteria

- Open rate ≥ 38% (industry news-newsletter benchmark).
- Click rate ≥ 6% (any link).
- Unsub rate ≤ 0.4% per send.
- Zero deliverability incidents in first 30 days.

## Rollback

If unsub > 1.5% in any send, pause next-day send + investigate subject / hero cluster.

## Cross-references

Master plan: S1390. Sister: S1381-S1389 set. Memory: `feedback_selfcheck_always.md`.
