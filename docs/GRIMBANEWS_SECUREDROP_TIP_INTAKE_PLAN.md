# GrimbaNews — SecureDrop / OnionShare Tip Intake Plan

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps) + investigative reporter (when hired)
**Walks:** Mythos S2203 (Long-form investigations multi-source intake) deferred → partial
**Gating dependency:** Anonymous-tip infra (S2025 partial via Wave LLL) + Tor hidden service hosting.

## Why this exists

Investigative journalism requires anonymous source intake. Public-press SecureDrop instances exist (NYT, Guardian, ProPublica). GrimbaNews-branded instance signals "we accept high-risk tips."

## v1 design

- SecureDrop server hosted on dedicated VPS (separate from main GrimbaNews infra).
- Tor hidden service: grimbanews-tips.onion (or generated address).
- Submission UI: minimal, no JS, no tracking.
- Per-submission encrypted GPG bundle stored.
- Per-investigator access via Tails OS + per-investigator GPG key.

## Per-tip workflow

1. Anonymous source uploads documents + message.
2. SecureDrop encrypts + queues.
3. Investigative reporter retrieves via Tails OS air-gap.
4. Per-tip triage: real lead vs noise.
5. Per-confirmed-lead: cross-reference + reporting.
6. Source-protection: never store identifying metadata.

## Security posture

- SecureDrop server isolated from main infra.
- 90-day retention on raw submissions; investigators move triaged to encrypted longterm.
- Per-quarter source-protection audit.

## Cross-references

Master plan: S2203. Sister: `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`, S2025 anonymous-tips intake.
