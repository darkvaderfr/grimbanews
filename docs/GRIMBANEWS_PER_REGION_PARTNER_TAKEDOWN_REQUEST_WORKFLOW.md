# GrimbaNews — Per-Region Partner Takedown Request Workflow

**Status:** plan v0
**Owner:** Sara Chen (CISO) + counsel + Lucy Leai (Strategy)
**Walks:** Mythos S1686 (per-region partner takedown request workflow) deferred → partial
**Gating dependency:** Per-partner SLA per Wave AAOO + ombudsman intake per Wave KKKK.

## Why this exists

Partners may request specific cluster takedowns (legal pressure, factual correction, partner-side editorial change). Workflow ensures 24h SLA (per syndication agreement) without compromising editorial independence.

## Workflow

1. Partner submits takedown request via `/partenaire/{slug}/takedown`:
   - Affected post(s) / cluster
   - Reason (factual | legal | partner-editorial | rights)
   - Supporting documents (if legal)
2. System logs in `partner_takedown_requests` (24h SLA clock starts).
3. Sara Chen + Lucy + per-region editor review:
   - Factual: route to corrections workflow + update doc.
   - Legal: depublish within 24h, escalate to counsel.
   - Partner-editorial: depublish + update partner content stream.
   - Rights: depublish + update license_notes.
4. Per-request resolution logged in transparency report.

## SLA compliance

- 24h response (acknowledgment) required.
- 7-day final decision required.
- Per-quarter SLA review.

## Cross-references

Master plan: S1686. Sister: `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`, `docs/GRIMBANEWS_DMCA_RIGHT_OF_REPLY_POLICY.md`.
