# GrimbaNews — DOM-TOM + Pacific Source Roster (Research List)

**Status:** research list v0 (feeds identified; license review + integration pending)
**Owner:** Lucy Leai (Strategy) — editorial pickup
**Walks:** Mythos S2161 (DOM-TOM source-roster expansion — scope) deferred → partial
**Gating dependency:** Per-source feed-URL verification + license review (per `news_sources.license_notes` slot per S140). Operator-side editorial pickup remaining.

## Why this exists

S2161 was honest-deferred as "operator-side editorial pickup; surrogate is RssFeedsSeeder + grimba:classify-sources cron from S1021 EU-east band." The cron exists but no curated list does. This doc is the curated list — research done, ready for editorial sign-off + technical seeding.

## Scope

DOM-TOM (Départements et Territoires d'Outre-Mer) and Pacific island states the global newscycle systematically under-covers.

## Source candidates (FR-language, primary)

### Guadeloupe / Martinique (S2162)

| Source | Type | URL pattern | Notes |
|---|---|---|---|
| RCI (Radio Caraïbes International) | Radio + web | rci.fm | Multi-island Caribbean; RSS likely under `/feed` |
| Outremers360 | News portal | outremers360.com | Pan-DOM-TOM coverage; strong RSS surface |
| France Antilles | Daily newspaper | https://www.guadeloupe.franceantilles.fr/ (+ martinique variant) | Print + web; RSS under `/rss/` |
| Martinique la 1ère | Public broadcaster | la1ere.francetvinfo.fr/martinique | RSS via Francetv standard |
| Guadeloupe la 1ère | Public broadcaster | la1ere.francetvinfo.fr/guadeloupe | Same |

### Guyane (S2163)

| Source | Type | URL pattern | Notes |
|---|---|---|---|
| Guyane la 1ère | Public broadcaster | la1ere.francetvinfo.fr/guyane | RSS |
| France-Guyane | Daily | franceguyane.fr | RSS under `/rss/` |
| Kourou Times | Local news | (verify) | Smaller; check freshness |

### Mayotte / Réunion (S2164)

| Source | Type | URL pattern | Notes |
|---|---|---|---|
| Mayotte la 1ère | Public broadcaster | la1ere.francetvinfo.fr/mayotte | RSS |
| Linfo.re | Réunion news portal | linfo.re | RSS |
| Clicanoo | Réunion daily | clicanoo.re | RSS |
| Imaz Press Réunion | News magazine | ipreunion.com | RSS |

### Polynésie / Nouvelle-Calédonie / Wallis-et-Futuna (S2165)

| Source | Type | URL pattern | Notes |
|---|---|---|---|
| Tahiti Infos | News portal | tahiti-infos.com | RSS |
| Polynésie la 1ère | Public broadcaster | la1ere.francetvinfo.fr/polynesie | RSS |
| Nouvelle-Calédonie la 1ère | Public broadcaster | la1ere.francetvinfo.fr/nouvellecaledonie | RSS |
| Les Nouvelles Calédoniennes | Daily | lnc.nc | RSS |
| Wallis-et-Futuna la 1ère | Public broadcaster | la1ere.francetvinfo.fr/wallisfutuna | RSS |

### Saint-Pierre-et-Miquelon / Saint-Barthélemy / Saint-Martin (S2166)

| Source | Type | URL pattern | Notes |
|---|---|---|---|
| Saint-Pierre-et-Miquelon la 1ère | Public broadcaster | la1ere.francetvinfo.fr/saintpierremiquelon | RSS |
| Le Pélican | SPM local | (verify) | Smaller |
| Le Journal de Saint-Barth | SBH local | journaldesaintbarth.com | Check RSS |
| Faxinfo SXM | Saint-Martin | faxinfo.fr | Check RSS |

## Source candidates (English-language Pacific, S2167-S2168)

### Pacific Beat / Pacific Islands

| Source | Type | URL pattern | Notes |
|---|---|---|---|
| RNZ Pacific (Radio New Zealand) | Public broadcaster | rnz.co.nz/international/pacific-news | RSS; high signal |
| Pacific Beat (ABC) | Australian public radio | abc.net.au/radio-australia/pacific-beat | RSS |
| Pacific Islands Report | News aggregator | pireport.org | RSS; check freshness |
| Fiji Times | Daily | fijitimes.com | RSS |
| Samoa Observer | Daily | samoaobserver.ws | RSS |
| Tonga Daily News | Daily | tongadailynews.to | RSS |
| Vanuatu Daily Post | Daily | dailypost.vu | RSS |
| Solomon Star | Daily | solomonstarnews.com | RSS |
| PNG Post-Courier | Daily | postcourier.com.pg | RSS |
| Cook Islands News | Daily | cookislandsnews.com | RSS |

## Source candidates (smaller AU states, S2169-S2170)

### Lusophone Africa / small island nations

| Source | Type | URL pattern | Notes |
|---|---|---|---|
| Expresso das Ilhas (Cabo Verde) | Daily | expressodasilhas.cv | PT-language; RSS |
| Tela Nón (São Tomé) | News portal | telanon.info | PT-language |
| Al-Watwan (Comoros) | News portal | alwatwan.net | FR/AR; check RSS |

### Smaller AU member states

| Source | Type | URL pattern | Notes |
|---|---|---|---|
| Lesotho Times | Daily | lestimes.com | EN |
| The Reporter (Lesotho) | Daily | thereporter.co.ls | EN |
| Times of Eswatini | Daily | times.co.sz | EN |
| Eritrea Hadas | Government news | shabait.com | Heavily state-affiliated — flag in `news_sources.ownership_type='state'` per S1066 |
| La Nation (Djibouti) | Daily | lanation.dj | FR; check RSS |
| RTNB (Burundi) | Public broadcaster | rtnb.bi | FR |

## Per-source intake template

For each source above, when seeded into `database/seeders/RssFeedsSeeder.php`:

```php
[
    'name' => 'RCI Guadeloupe',
    'url' => 'https://rci.fm/feed',  // VERIFY
    'editorial_category' => 'general',
    'region' => 'pacific',  // or 'sub_saharan' / 'caribbean' per `App\Ground\Regions`
    'language' => 'fr',
    'ownership_type' => 'commercial',
    'license_notes' => 'Aggregation under fair-use; headlines + lede only; full-article retrieval per source ToS — to verify',
    'credibility_score' => null,  // operator-fill after vetting
    'bias_rating' => null,         // operator-fill
    'factuality_score' => null,    // operator-fill
],
```

## Pre-launch verification checklist (per source)

For each candidate above, before seeding:

1. Confirm RSS feed URL responds 200 with valid Atom/RSS XML.
2. Confirm feed update frequency (target: ≥ daily refresh).
3. Confirm full-article URL pattern (for `GrimbaArticleText` extraction).
4. Read source ToS for aggregation/redistribution rights — populate `license_notes`.
5. Assign initial `credibility_score`, `bias_rating`, `factuality_score` (Lucy Leai or future content editor).
6. Tag `ownership_type` correctly per S1066/S1067 (state / nonprofit / commercial).

## Editorial considerations

- **Linguistic mix:** FR-primary for DOM-TOM; EN-primary for Pacific Anglophone; PT for Lusophone Africa; mixed FR/AR for Maghreb island states.
- **Per-region credibility calibration:** local-source credibility differs from international newswire credibility. Per S2173 baseline TBD.
- **State-media flagging:** Eritrea, Djibouti, Burundi state broadcasters require explicit `state` ownership tagging per S1066.
- **Content velocity:** these sources publish less per day than majors; weight in scoring accordingly so they don't get drowned out by Reuters/AFP.

## Integration steps (when editorial sign-off + per-source verification complete)

1. Append rows to `database/seeders/RssFeedsSeeder.php` (additive only).
2. Run `php artisan db:seed --class=RssFeedsSeeder` (idempotent).
3. Run `php artisan grimba:classify-sources --apply --sync-posts` (per S1021 daily cron at `routes/console.php:222-226`).
4. Wait one full ingest cycle (next RSS poll, hourly typically).
5. Spot-check articles in `/admin/grimba/sources` admin board for correct region/category.
6. Verify in editorial dashboard.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2161 row; gates for S2162-S2170)
- Region taxonomy: `app/Ground/Regions.php` (already lists Pacific + Antarctica)
- Source seeder: `database/seeders/RssFeedsSeeder.php`
- Classifier: `app/Services/GrimbaSourceClassifier.php`
- Per-source license-notes column: `news_sources.license_notes` (per S140)
- Existing sub-Saharan precedent (S1024 surrogate): `app/Console/Commands/GrimbaSeedImmigrationSources.php` (La Cimade + UNHCR)
- Editorial pivot context: `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`
