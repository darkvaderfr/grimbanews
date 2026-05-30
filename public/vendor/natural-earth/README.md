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
2. **Added a canonical `iso2` join key** (see below).
3. **Rounded all coordinates + label points to 3 decimal places** (~111 m
   precision, ample for a 1:110m basemap).

Result: **220 KB raw / 70 KB gz**, 177 features — comfortably inside the
≤250 KB gz performance budget.

`LABEL_X` / `LABEL_Y` are retained on purpose: `App\Support\CountryCentroids`
(S-MAP-V4-04) derives its ISO-2 → `[lat, lng]` table from these label points
so the pins land consistently with the drawn boundaries.

## Join on `iso2`, NOT on `ISO_A2` (Phase-3 trap)

**Always join the country-fill layer (V4-09) to pin / bias data on the
canonical `iso2` property — never on raw `ISO_A2`.**

Natural Earth leaves `ISO_A2` as the sentinel `"-99"` for **5 features**, not
2. Three of them have a perfectly valid code that lives only in `ISO_A2_EH`:

| Feature | `ISO_A2` | `ISO_A2_EH` | canonical `iso2` |
|---|---|---|---|
| France | `-99` | `FR` | `FR` |
| Norway | `-99` | `NO` | `NO` |
| Kosovo | `-99` | `XK` | `XK` |
| N. Cyprus | `-99` | `-99` | `null` |
| Somaliland | `-99` | `-99` | `null` |

A naïve join on `ISO_A2` would fail to match **France** — the flagship country
for a French-first news aggregator — leaving its boundary dark on the map.

The slim step therefore writes a canonical **`iso2`** property on every
feature: `ISO_A2_EH` when `ISO_A2` is `-99`, otherwise `ISO_A2`. It is `null`
only for the two genuinely code-less disputed territories (N. Cyprus,
Somaliland), which consequently have no centroid join — expected, not a bug.
`ISO_A2` / `ISO_A2_EH` are kept alongside it for reference.

To refresh: re-fetch the upstream file at the desired tag, re-run the slim
step above (whitelist + canonical `iso2` + round), and re-test `/breaking-map`.
