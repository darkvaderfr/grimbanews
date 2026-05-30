# Vendored Natural Earth world boundaries

`world.geojson` is the country-boundary layer drawn under the pins on the
S-MAP-V4 `/breaking-map` view (Leaflet country fill layer).

## Source

- Dataset: **Natural Earth — Admin 0 Countries, 1:110m** (`ne_110m_admin_0_countries`)
- Upstream: <https://github.com/nvkelso/natural-earth-vector> (tag `v5.1.2`)
  `geojson/ne_110m_admin_0_countries.geojson`
- Project home: <https://www.naturalearthdata.com>

## License

**Public Domain.** Natural Earth is released into the public domain — no
permission, attribution, or fee required (an attribution link is appreciated
but not mandatory). Because it is map data (not an LLM provider), surfacing
"Natural Earth" anywhere is compatible with the NobuAI brand rule.

## How this file was produced (reproducible)

Fetched the raw 1:110m countries GeoJSON (838 KB raw / 210 KB gz, 168
properties per feature) and slimmed it for the web:

1. **Whitelisted 9 properties** — `ISO_A2`, `ISO_A2_EH`, `ISO_A3`, `NAME`,
   `NAME_EN`, `CONTINENT`, `REGION_UN`, `LABEL_X`, `LABEL_Y` (the rest — 159
   columns of FIPS / WB / GDP / mapcolor metadata — were dropped).
2. **Rounded all coordinates + label points to 3 decimal places** (~111 m
   precision, ample for a 1:110m basemap).

Result: **217 KB raw / 70 KB gz**, 177 features — comfortably inside the
≤250 KB gz performance budget.

`LABEL_X` / `LABEL_Y` are retained on purpose: `App\Support\CountryCentroids`
(S-MAP-V4-04) derives its ISO-2 → `[lat, lng]` table from these label points
so the pins land consistently with the drawn boundaries.

N. Cyprus and Somaliland carry no standard ISO-2 code (disputed territories)
and therefore have no centroid join — expected, not a bug.

To refresh: re-fetch the upstream file at the desired tag, re-run the slim
step above, and re-test `/breaking-map`.
