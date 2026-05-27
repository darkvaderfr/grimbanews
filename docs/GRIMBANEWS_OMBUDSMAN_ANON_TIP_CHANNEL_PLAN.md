# GrimbaNews — Ombudsman Anonymous Tip Channel Plan

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Maya Patel (Compliance) + Lucy Leai (Strategy)
**Walks:** Mythos S2025 (ombudsman intake — anonymous tip channel) deferred → partial
**Gating dependency:** SecureDrop or equivalent + dedicated server / hosted instance + per-jurisdiction legal review.

## Why this exists

A real ombudsman office must accept anonymous tips (sources can't always reveal identity safely). The bar is high: standard email form leaks IP / metadata; SecureDrop-equivalent is the floor.

## v1 design decision (build vs adopt)

| Option | Pros | Cons |
|---|---|---|
| SecureDrop self-host | Industry standard, FPF-supported | Tor-only, ops overhead, dedicated server |
| Hush Line (FPF lightweight) | Easier setup, OSS | Newer, smaller community |
| Globaleaks | Mature, EU-focused | Setup complexity |
| Generic encrypted form | Lower bar | Insufficient anonymity guarantees |

v1 recommendation: **Hush Line self-host** for v1 (lower ops cost than SecureDrop), upgrade to SecureDrop when ombudsman office matures.

## Surface

- `/ombudsman/conseil-anonyme` documents the path (Tor URL, PGP key, expected response time).
- Public PGP key published with signed fingerprint.
- Per-tip case-number assignment for tipster to track without identifying themselves.

## Operational guardrails

- Air-gapped review machine.
- No logs, no analytics on the tip-intake surface.
- Mandatory dual-control before any external action.
- Annual third-party audit of the setup.

## Cross-references

Master plan: S2025. Sister: S2021 (charter), S2022 (first hire), S2034 (correction authority), S2040 (launch retro).
