# GrimbaNews — Niche Topic v2 Scope (Climate / Science / Culture deep)

**Status:** scope v0 (per-bucket source rosters drafted; per-bucket briefs deferred)
**Owner:** Lucy Leai (Strategy) + Steve Jobs (CPO) on rail design
**Walks:** Mythos S2181 (niche-topic v2 — scope decision) deferred → partial
**Gating dependency:** Editorial sign-off + per-source license verification. Scope itself is operator-side.

## Why this exists

S2181 was honest-deferred as "operator-side editorial." The scope decision — which buckets to deepen, what sources to add per bucket, what rail design — is operator-side work that doesn't depend on third parties. This doc proposes the scope, drafts per-bucket source rosters, and lists per-bucket briefs to write.

## Today's state

`App\Services\GrimbaCategoryClassifier::CATEGORIES` ships Climate, Science, Culture as flat keyword-driven buckets. Per S1033 each bucket pulls from a per-category source pool resolved via `news_sources.editorial_category`. v2 deepens each bucket with: deeper source roster, per-bucket editorial brief, per-sub-bucket taxonomy where relevant.

## Bucket priorities

Recommend deepening in this order:

1. **Climate** — high reader demand, clear authoritative source set, ~30 quality FR + EN feeds available.
2. **Science** — high reader demand, harder source set (preprint servers, press-release feeds), needs more curation.
3. **Culture** — broadest sub-bucket variation; easier to extend incrementally.
4. **Technology v2** + **Health v2** + **Sports v2** — secondary phase per S2194-S2196.

## Climate v2 deep roster (S2182)

| Source | Lang | Type | URL pattern | Notes |
|---|---|---|---|---|
| Carbon Brief | EN | News + analysis | carbonbrief.org | Strong climate-science/policy split; RSS |
| Inside Climate News | EN | Pulitzer-winning investigative | insideclimatenews.org | High credibility |
| Reporterre | FR | Eco journalism | reporterre.net | RSS |
| Vert | FR | Climate magazine | vert.eco | Newer; daily |
| Mongabay | EN (+ ES + FR sub-sites) | Forests / biodiversity | mongabay.com | High signal for sub-Saharan + Amazonia |
| Climate Home News | EN | Climate-policy + COP coverage | climatechangenews.com | RSS |
| The Conversation (Environment + Energy sections) | Multi-lang | Academic-authored | theconversation.com/global/environment-energy | RSS |
| Le Monde Planète | FR | Mainstream climate desk | lemonde.fr/planete/rss_full.xml | Already in source pool — promote to climate-bucket weighting |
| BBC Climate | EN | Mainstream climate desk | bbc.com/news/science_and_environment | RSS |
| IPCC | EN | Press releases / reports | ipcc.ch | Lower frequency; check feed cadence |

### Climate v2 sub-buckets (proposed)

- **Climate science** (research findings, IPCC reports, peer-review).
- **Climate policy** (COP, EU Green Deal, national policy, court cases).
- **Climate impact** (extreme weather, sea-level, biodiversity).
- **Energy transition** (renewables, nuclear, grids, EVs).
- **Adaptation + finance** (loss-and-damage, just transition, climate finance).

Each sub-bucket gets its own keyword set in `GrimbaCategoryClassifier::CATEGORIES['climate_*']` (additive — keep flat bucket as union).

## Science v2 deep roster (S2186-S2190)

### News (S2187)

| Source | Lang | Type | URL pattern | Notes |
|---|---|---|---|---|
| Nature News | EN | Top-journal news desk | nature.com/news | RSS |
| Science Magazine News | EN | Top-journal news desk | science.org/news | RSS |
| The Lancet | EN | Public-health briefings | thelancet.com | RSS |
| New Scientist | EN | Magazine | newscientist.com | RSS |
| Pour la Science | FR | French Scientific American | pourlascience.fr | RSS |
| Sciences et Avenir | FR | Mainstream science | sciencesetavenir.fr | RSS |
| Quanta Magazine | EN | Math / physics / biology long-form | quantamagazine.org | RSS |
| Ars Technica Science | EN | Mass-market | arstechnica.com/science | RSS |

### University press releases (S2189)

| Source | Lang | Type | URL pattern | Notes |
|---|---|---|---|---|
| EurekAlert! | EN | Press-release aggregator | eurekalert.org | Open-access RSS feeds per-discipline |
| AlphaGalileo | EN | EU press-release aggregator | alphagalileo.org | Free RSS |
| Inserm Salle de Presse | FR | French medical research | presse.inserm.fr | RSS |
| CNRS Le Journal | FR | French national research | lejournal.cnrs.fr | RSS |

### Preprint servers (S2186)

| Server | Lang | Discipline | Notes |
|---|---|---|---|
| arXiv | EN | Physics / Math / CS | Not RSS-native; needs custom adapter |
| bioRxiv | EN | Biology | Same |
| medRxiv | EN | Medicine | Same |
| HAL | FR/EN | French open-archive | API-based ingest needed |

Deferred sub-task: build a small adapter in `app/Services/` for arXiv API → `posts` table (handles their JSON format; respects rate limits). Lands as a separate ship sprint.

### Per-discipline buckets (S2190)

Proposed sub-buckets: `science_physics`, `science_biology`, `science_climate` (overlap with climate bucket — cross-tag), `science_ai_ml`, `science_medicine`, `science_space`, `science_environment` (overlap), `science_other`.

## Culture v2 deep roster (S2191-S2193)

### Sub-buckets (S2191)

- **Books** (reviews, publishing-industry, author interviews).
- **Film** (releases, festivals, criticism).
- **Music** (releases, industry, criticism, live).
- **Theater + performing arts** (premieres, festivals).
- **Visual art** (museum, exhibitions, art-market).
- **Diaspora cultures** (African, Caribbean, francophone-postcolonial — overlaps with regions taxonomy).

### Sources (subset; extend per sub-bucket)

| Source | Sub-bucket | Lang | URL pattern |
|---|---|---|---|
| Le Monde des Livres | Books | FR | lemonde.fr/livres |
| The Guardian Books | Books | EN | theguardian.com/books |
| Variety | Film | EN | variety.com |
| Cahiers du Cinéma | Film | FR | cahiersducinema.com |
| Pitchfork | Music | EN | pitchfork.com |
| Les Inrockuptibles | Music + culture | FR | lesinrocks.com |
| Theatermania | Theater | EN | theatermania.com |
| ARTNews | Visual art | EN | artnews.com |
| Beaux Arts Magazine | Visual art | FR | beauxarts.com |
| Brittle Paper | Books (African literature) | EN | brittlepaper.com |
| Le Point Afrique Culture | Culture (diaspora overlap) | FR | lepoint.fr/afrique |

## Per-bucket editorial brief (S1032 + S2183-S2185 surrogates)

Each deepened bucket gets a **one-page brief** living in `docs/editorial-briefs/{bucket}.md` (directory does not yet exist; create on first brief). Brief template:

```markdown
# {Bucket} Editorial Brief

**Purpose:** What's the reader value proposition for this bucket?
**Coverage promises:** What we commit to cover. What we won't cover.
**Source weighting:** Which sources get top placement; which are caution-flagged.
**Per-event playbooks:** e.g. "When an IPCC report drops, here's our coverage protocol."
**Sub-bucket routing:** How sub-buckets resolve to rails / chips / landing pages.
**NobuAI prompt overlay (S2172 surrogate):** Per-bucket nuance the global NobuAI prompt should respect.
**Quality bar:** Minimum sourcing standard before publishing.
```

First three briefs to write: Climate, Science, Culture (matches deepening order).

## Integration steps

1. **Editorial sign-off** — Lucy Leai approves per-bucket scope + source roster.
2. **Per-source verification** — same per-source checklist as `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md`.
3. **Seeder additions** — append to `RssFeedsSeeder.php` (additive only).
4. **Classifier refinement** — add per-sub-bucket keyword maps to `GrimbaCategoryClassifier::CATEGORIES['climate_*']` etc.
5. **Editorial briefs** — write the three briefs.
6. **Rail design** — Steve Jobs reviews how sub-buckets surface on `/categorie/climat` landing.
7. **Backfill** — `php artisan grimba:backfill-category --apply` to re-tag historical posts.
8. **Test additions** — add per-bucket fixture posts + classifier-coverage tests.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2181 row; gates for S2182-S2200)
- Classifier: `app/Services/GrimbaCategoryClassifier.php`
- Backfill command: `app/Console/Commands/GrimbaBackfillCategory.php`
- Source seeder: `database/seeders/RssFeedsSeeder.php`
- Sister roster: `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md` (per-source intake template borrowed)
- Per-source license slot: `news_sources.license_notes`
- Editorial pivot context: `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`
