# Vendored Leaflet + Leaflet.markercluster

These assets are **self-hosted** (vendored) rather than loaded from a CDN.
Reason: CSP-safe (no third-party script origin to allowlist), no runtime
dependency on an external host, and reproducible builds. They power the
S-MAP-V4 real-map rebuild at `/breaking-map` (Leaflet + Natural Earth
GeoJSON + CARTO Dark Matter tiles).

## Contents

| File | Source | Purpose |
|---|---|---|
| `leaflet.js` | leaflet@1.9.4 `dist/leaflet.js` | Core map engine (minified, production) |
| `leaflet.css` | leaflet@1.9.4 `dist/leaflet.css` | Core map styles |
| `images/*` | leaflet@1.9.4 `dist/images/` | Default marker + layers-control icons |
| `leaflet.markercluster.js` | leaflet.markercluster@1.5.3 `dist/leaflet.markercluster.js` | Marker clustering plugin (minified) |
| `MarkerCluster.css` | leaflet.markercluster@1.5.3 `dist/MarkerCluster.css` | Cluster base styles |
| `MarkerCluster.Default.css` | leaflet.markercluster@1.5.3 `dist/MarkerCluster.Default.css` | Cluster default theme (overridden by our HUD styles) |
| `LICENSE-leaflet.txt` | leaflet@1.9.4 `LICENSE` | BSD 2-Clause |
| `LICENSE-markercluster.txt` | leaflet.markercluster@1.5.3 `MIT-LICENCE.txt` | MIT |

Source maps (`*.js.map`) and unminified `*-src.js` builds were intentionally
omitted to keep the served payload lean.

## Versions + licenses

- **Leaflet 1.9.4** — BSD 2-Clause, © 2010-2023 Volodymyr Agafonkin.
  <https://leafletjs.com> · <https://github.com/Leaflet/Leaflet>
- **Leaflet.markercluster 1.5.3** — MIT, © Dave Leaver.
  <https://github.com/Leaflet/Leaflet.markercluster>

Both are open-source mapping libraries (not LLM providers); per the NobuAI
brand rule their attribution on the reader surface is fine.

## How these were vendored (reproducible)

```sh
npm pack leaflet@1.9.4 leaflet.markercluster@1.5.3
tar -xzf leaflet-1.9.4.tgz
tar -xzf leaflet.markercluster-1.5.3.tgz
# copy package/dist/{leaflet.js,leaflet.css,images} and
# package/dist/{leaflet.markercluster.js,MarkerCluster*.css} here
```

To upgrade: bump the versions above, re-run, and re-test `/breaking-map`
(the S-MAP-V4 lock tests assert these asset paths resolve).
