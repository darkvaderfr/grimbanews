# GrimbaNews — Per-Region Partner Escalation Path

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO) + counsel
**Walks:** Mythos S1694 (per-region partner escalation path) deferred → partial
**Gating dependency:** First-partner escalation incident.

## Tiered escalation

| Tier | Trigger | Contact | SLA |
|---|---|---|---|
| L1 | Tech / SLA issue | Jacob Lee (DevOps) | 24h response |
| L2 | Editorial dispute | Per-region editor + Lucy Leai | 48h response |
| L3 | Brand-safety incident | Sara Chen (CISO) + Lucy | 24h response |
| L4 | Legal / contract dispute | Counsel + Lucy + Vader | 7-day response |

## Per-tier playbook

- L1: tech-support ticket workflow.
- L2: editorial review meeting within 48h.
- L3: incident response per `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`.
- L4: counsel engaged + per-clause review.

## Communication template

```
Subject: GrimbaNews Partner Escalation — {Tier} — {Partner Name}

We've received your concern about {brief description}.

Tier: {L1 | L2 | L3 | L4}
Owner: {name}
SLA: {response time}

We'll be in touch by {date}.
```

## Cross-references

Master plan: S1694. Sister: `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`, `docs/GRIMBANEWS_PER_REGION_PARTNER_TAKEDOWN_REQUEST_WORKFLOW.md`.
