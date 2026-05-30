# /breaking-map — S-MAP-V4 visual baselines

Reference screenshots of the v4 real-map (`/breaking-map`) for manual /
future-automated visual regression. The map is **dark-only by design** (CARTO
Dark Matter + the futurist HUD), so dark is the only baseline.

| File | Viewport | Locale |
|---|---|---|
| `baseline-desktop-en.png` | 1440×900 | `?lang=en` |
| `baseline-desktop-fr.png` | 1440×900 | `?lang=fr` |
| `baseline-mobile-en.png` | 390×844 | `?lang=en` |
| `baseline-mobile-fr.png` | 390×844 | `?lang=fr` |

Each shows: the LIVE/HUD chrome, the 5 bias filter chips (default-on), the
CARTO + Natural Earth basemap with bias-mix donut markers + purple cluster
bubbles, and the continent sidecar (desktop right panel / mobile bottom-sheet).

## How they were captured (S-MAP-V4-20)

`php artisan serve` on :8000, driven with Playwright:

```
viewport -> /breaking-map?window=720&lang=<en|fr>
hide cookie-consent banner, scrollIntoView('.gmap-stage'), wait for tiles+pins
screenshot (png, css scale)
```

To refresh after a visual change: re-capture at the same 2 viewports × 2
locales and diff against these. (window=720 is used because the seeded dev DB's
freshest posts can be older than the default 18h window.)
