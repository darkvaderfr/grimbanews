# GrimbaNews — Multi-Decade Preservation Scope Decision

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Larry Ellison (DBA) + Vader + Ray Dalio (CFO) + counsel
**Walks:** Mythos S2221 (Multi-decade preservation scope + retention horizon) deferred → partial
**Gating dependency:** Vader + counsel + Ray cost review.

## Why this exists

Journalism's historical value depends on permanent preservation. GrimbaNews articles 50 years from now will be cited in research, court, public discourse. Default DB lifecycle isn't built for this — needs deliberate archival.

## Retention horizon options

- **10 years:** matches typical commercial DB lifecycle.
- **25 years:** matches archival-newspaper convention.
- **50 years:** matches NYT / Le Monde / BBC archive standard.
- **Indefinite (100+ years):** matches national-library policy.

## Recommendation

50 years minimum. Stretch goal: indefinite via Internet Archive partnership (Wave SUB-14 sister) + IIPC membership (S2223).

## Per-archival-tier strategy

- **Hot (last 5 years):** Live in main DB, fully searchable.
- **Warm (5-15 years):** Live in DB with cold-tier paging.
- **Cold (15+ years):** Read-only DB shard + Internet Archive snapshot + per-year offline backup.
- **Deep archive (50+ years):** Magnetic tape + national-library deposit.

## Per-decade cost estimate

- Hot DB: ~$50/mo per GB.
- Cold DB: ~$5/mo per GB.
- Internet Archive: free (per Wayback).
- IIPC: ~€2k/yr membership.
- National-library deposit: ~free (operator side).
- Total per-decade for 50-year horizon: ~€500k cumulative.

## Cross-references

Master plan: S2221. Sister: S2222 Wayback partnership, S2223 IIPC.
