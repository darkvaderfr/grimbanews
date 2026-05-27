# GrimbaNews — NobuAI Hallucination Corpus Growth Plan

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Sara Chen (CISO) + Lucy Leai (Strategy)
**Walks:** Mythos S1090 (NobuAI hallucination-corpus growth) deferred → partial
**Gating dependency:** Reader-feedback channel for hallucination reports.

## Why this exists

Reader-reported hallucinations are the ground truth for our hallucination-detector pipeline (per `docs/GRIMBANEWS_HALLUCINATION_DETECTOR_PLAN.md` Wave ZZZZ). Without a structured channel, we lose this signal.

## Reader-side: report-hallucination flow

On every NobuAI-generated summary/insight, a small "Signaler une erreur" link surfaces. Click opens a per-report form:

```
- Original NobuAI output (read-only)
- Original source article link (read-only)
- What's wrong? (free-text + category dropdown: "wrong date", "wrong name", "fabricated quote", "wrong fact", "missing context", "other")
- Reader email (optional, for follow-up)
```

## Server-side: corpus growth

Each report stores in `nobuai_hallucination_reports`:

```
id | post_id | summary_field | original_output | report_category | report_text
   | reporter_email | reporter_member_id | created_at | status (pending|valid|invalid|fixed)
```

Editor reviews weekly:
- Confirms hallucination or rejects.
- For confirmed: drops the bad summary, regenerates via verifier pipeline (Wave YYYY), marks `fixed`.
- For false-positive: marks `invalid`, no UI change.

## Corpus use

Confirmed-hallucination corpus → adversarial test set for new NobuAI driver onboarding. Any new driver must score ≥ 95% on the corpus before promotion.

## Cadence

- Reader reports: real-time intake.
- Editor review: weekly batch.
- Adversarial test run: per-driver-upgrade.
- Corpus retention: indefinite (small data, high signal).

## Cross-references

Master plan: S1090. Sister: `docs/GRIMBANEWS_HALLUCINATION_DETECTOR_PLAN.md`, `docs/GRIMBANEWS_AGENT_VERIFIER_HARNESS_PLAN.md`.
