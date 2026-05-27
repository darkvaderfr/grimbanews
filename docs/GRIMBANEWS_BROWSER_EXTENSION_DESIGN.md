# GrimbaNews — Browser Extension Design

**Status:** plan v0 (no extension shipped)
**Owner:** Nina Patel (Lead FE) + Liam Smith (PM) + Steve Jobs (CPO)
**Walks:** Mythos S1167 (browser extension) deferred → partial — sister to mobile pack Wave DDDD
**Gating dependency:** Chrome Web Store + Firefox Add-Ons account onboarding.

## Concept

Lightweight browser extension that surfaces GrimbaNews context when reader visits any news article:
- Detects publisher (parses URL against `news_sources` table)
- Looks up source's bias + factuality + ownership badge from public API
- Surfaces matching GrimbaNews cluster ("3 other outlets covering this story") if available
- Click → opens cluster on grimbanews.com

## Tech stack

- Manifest v3 (Chrome + Firefox compat)
- Vanilla JS popup (no framework — tiny bundle)
- Talks to `/api/middle-ground.json` + `/api/sources.json` (gates on Wave AACC B2B Trust Score API for sources endpoint)
- No reader tracking; popup data is per-page

## Popup UI

```
┌─────────────────────────────────────┐
│ GrimbaNews                          │
├─────────────────────────────────────┤
│ Source: Le Monde                    │
│ Biais éditorial: ⬤ Gauche-Centre   │
│ Fiabilité: 8/10                     │
│ Propriétaire: Privé (Niel)         │
│                                     │
│ ↗ Voir 4 autres couvertures (FR)   │
│                                     │
│ ─────────────────────────────────── │
│ Méthodologie · Désinstaller        │
└─────────────────────────────────────┘
```

## Permissions

- `activeTab` — read current URL only (no background tracking)
- No `storage` — settings via `/account` page after login
- No reader-data collection

## Distribution

- Chrome Web Store
- Firefox Add-Ons
- Edge auto-imports from Chrome Store
- Safari extension via separate Xcode build (gates on Apple Dev account)

## Cross-references

Master plan: S1167. Sister: Wave DDDD mobile pack, `docs/GRIMBANEWS_MIDDLE_GROUND_API_REFERENCE.md`.
