# GrimbaNews — Per-Category Granular Consent Toggles

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Nina Patel (Lead FE) + Steve Jobs (CPO)
**Walks:** Mythos S1865 (per-category granular consent toggles) deferred → partial
**Gating dependency:** Cookie purpose classification (Wave SUB-48 sister).

## v1 banner design

Banner v1 today: binary accept/reject. v2 design with per-category granularity:

```
┌──────────────────────────────────────────────┐
│ Vos préférences cookies                       │
├──────────────────────────────────────────────┤
│ ☑ Strictement nécessaires (toujours actif)   │
│ ☐ Fonctionnels (mémoriser préférences)       │
│ ☐ Analyse audience (aide-nous à améliorer)   │
│ ☐ Publicité (annonces personnalisées)        │
│                                              │
│ [Personnaliser]  [Tout accepter]  [Tout refuser] │
└──────────────────────────────────────────────┘
```

## Per-toggle state machine

Per-reader state stored in `grimba_consent_v` cookie:
```json
{
  "version": "v2",
  "strict": true,
  "functional": false,
  "analytics": false,
  "advertising": false,
  "consent_timestamp": "2026-MM-DDTHH:MM:SSZ"
}
```

## Per-toggle change auditing

Per-toggle change logged per Wave LLL consent log design:
- Per-event: timestamp + category + old_state → new_state.
- Per-event retention: 13 months minimum (proof of consent).

## Per-locale variants

- EU + UK + Brazil: opt-in default (everything unchecked except strict).
- US CA + VA + CO: opt-out variant (everything checked except advertising).
- Per-locale per Wave AAII per-region cookie consent variants.

## Cross-references

Master plan: S1865. Sister: `docs/GRIMBANEWS_COOKIE_PURPOSE_CLASSIFICATION.md`, `docs/GRIMBANEWS_PER_REGION_COOKIE_CONSENT_VARIANTS.md`.
