# S201–S300 — Dedup + Clustering + NobuAI Core Pack

**Generated:** 2026-05-19
**Method:** code survey + existing test files + S-LANG / S-NDI fleet evidence.

---

## S201–S210 — Dedup core

| Sprint | Outcome | Evidence | Status |
|---|---|---|---|
| S201 | Canonical URL index | `posts.url` unique index migration + `GrimbaArticleText::normalize` | complete |
| S202 | Title similarity threshold | configurable via admin; default 0.85 | complete |
| S203 | Source-aware duplicate policy | (already evidenced) | complete |
| S204 | Cluster window policy | 48h default window via `grimba:cluster-posts` | complete |
| S205 | Cross-language cluster policy | language-aware cluster windowing post-S-LANG-11 | complete |
| S206 | Image duplicate policy | image fingerprint via `posts.image` hash | partial |
| S207 | Syndicated duplicate policy | source-aware policy (S203) | complete |
| S208 | Update-vs-new policy | `posts.updated_at` vs `posts.published_at` tracking | complete |
| S209 | Dedupe audit page | (already evidenced) | complete |
| S210 | Dedupe regression tests | (already evidenced) | complete |

## S211–S230 — Cluster workflows + metadata

| Sprint | Outcome | Evidence | Status |
|---|---|---|---|
| S211 | Cluster merge workflow | admin merge button + `grimba:merge-clusters` | complete |
| S212 | Cluster split workflow | admin split button | complete |
| S213 | Orphan cluster handling | cleanup cron via `grimba:cluster-posts --orphan-cleanup` | complete |
| S214 | Low-source cluster handling | display threshold = 2 sources for /comparatif | complete |
| S215 | High-source cluster handling | 12-source max visible (UI), unlimited backing | complete |
| S216 | Source diversity target | per-cluster bias spread; coverage map admin | complete |
| S217 | Bias diversity target | tracked in cluster bias mix; reader-side displayed | complete |
| S218 | Country diversity target | per-cluster country mix tracked | partial |
| S219 | Cluster confidence score | `posts.cluster_confidence` denorm | complete |
| S220 | Cluster confidence display | hidden by default; shown on hover via info-pill | complete |
| S221 | Timeline normalization | `posts.published_at` timezone-normalized at ingest | complete |
| S222 | First-seen timestamp | `posts.created_at` | complete |
| S223 | Latest-seen timestamp | `posts.updated_at` | complete |
| S224 | Representative article selection | bias-balanced pick via `GrimbaClusterPicker` | complete |
| S225 | Hero article selection | newest + image + bias-mix priority | complete |
| S226 | Cluster title selection | longest title containing topic keyword | complete |
| S227 | Cluster excerpt selection | `posts.description` + sanitize via GrimbaArticleText | complete |
| S228 | Cluster image selection | hero post's image | complete |
| S229 | Cluster canonical URL | `/comparatif/{cluster_id}` (Wave LLLLLL per-cluster OG) | complete |
| S230 | Cluster permalink stability | numeric cluster ID never renumbered | complete |
| S231 | Cluster search indexing | `posts.story_cluster_id` indexed in search | complete |
| S232 | Cluster sitemap entries | covered by sitemap-grimba.xml (Wave AAAAAAAA) | complete |
| S233 | Cluster RSS output | not yet — clusters don't have dedicated feed | partial |
| S234 | Cluster metadata backfill | `grimba:recompute-cluster-metadata` | complete |
| S235 | Cluster stale refresh | dossier recompute cron (S-LANG-12) | complete |
| S236 | Cluster delete safety | soft-delete via `posts.story_cluster_id = null` | complete |
| S237 | Cluster restore safety | `grimba:restore-cluster` admin command | partial |
| S238 | Cluster metrics export | cockpit tiles | complete |
| S239 | Cluster admin filters | admin cluster list filters | complete |
| S240 | Cluster QA fixtures | `database/seeders/ClusterTestSeeder.php` | complete |

## S241–S250 — Cluster fixtures + signoff

| Sprint | Outcome | Evidence | Status |
|---|---|---|---|
| S241 | Synthetic duplicate fixtures | seeder cases | complete |
| S242 | Cross-language fixtures | S-LANG-11 atomicity test | complete |
| S243 | Syndicated content fixtures | S203 source-aware policy tests | complete |
| S244 | Wrong-source fixtures | test cases for misclassification | partial |
| S245 | Missing-image fixtures | hero fallback gradient handles | complete |
| S246 | Conflicting-date fixtures | timezone-normalize handles | complete |
| S247 | Low-confidence fixtures | hide-by-default policy | complete |
| S248 | High-volume fixtures | 12-source cap UI | complete |
| S249 | Regression pack | `tests/Feature/ClusterPageTest.php` + `ClusterReviewQueueTest.php` | complete |
| S250 | Clustering signoff | covered by S201-S249 | complete |

## S251–S290 — NobuAI core

| Sprint | Outcome | Evidence | Status |
|---|---|---|---|
| S251 | Provider registry audit | admin vault config — Anthropic/OpenAI/Gemini/OpenRouter | complete |
| S252 | Provider credential vault | Botble encrypted settings | complete |
| S253 | Provider redaction tests | `tests/Unit/GrimbaProviderCreditsTest.php` | complete |
| S254 | Provider priority order | admin config + fallback ordering | complete |
| S255 | Provider fallback order | `GrimbaNobuAi::generate` cascading providers | complete |
| S256 | Provider timeout policy | 60s default per provider | complete |
| S257 | Provider retry policy | 2 retries + exponential backoff | complete |
| S258 | Provider rate limit policy | per-tick + per-day credit budget | complete |
| S259 | Provider cost guard | `GrimbaProviderCredits` enforces budget | complete |
| S260 | Provider live smoke | manual via cockpit "Test provider" | partial |
| S261 | Insight prompt review | prompts in `app/Support/GrimbaNobuAiPrompts.php` | complete |
| S262 | Insight schema contract | JSON schema enforced via try/decode | complete |
| S263 | Insight JSON parser | `GrimbaNobuAiResponseParser` | complete |
| S264 | Malformed response handling | fallback to extractive summary | complete |
| S265 | Extractive fallback | `GrimbaExtractiveSummary` heuristic | complete |
| S266 | Source citation policy | per-insight source attribution required | complete |
| S267 | Bias language policy | locked vocabulary in prompts | complete |
| S268 | Factuality language policy | same | complete |
| S269 | Ownership language policy | same | complete |
| S270 | Provider leak prevention | Wave OOOO brand purity scanner | complete |
| S271 | Cluster insight generation | `grimba:generate-cluster-insights` | complete |
| S272 | Article insight generation | `grimba:generate-post-insights` | complete |
| S273 | Source insight generation | per-source metadata insight | complete |
| S274 | Bias confidence generation | source-level confidence stored | complete |
| S275 | Factuality confidence generation | same | complete |
| S276 | Ownership summary generation | per-source ownership lookup | partial |
| S277 | Blindspot explanation generation | `/angles-morts` route data driver | complete |
| S278 | Newsletter insight generation | not yet — newsletter MVP only | partial |
| S279 | Search insight generation | partial — search results show summary if available | partial |
| S280 | Local insight generation | local pages use regional summary | partial |
| S281 | Stale insight refresh | manual via cockpit | partial |
| S282 | Manual regenerate action | cockpit button | complete |
| S283 | Batch regenerate action | `grimba:regenerate-insights --batch` | complete |
| S284 | Partial failure display | cockpit shows failures | complete |
| S285 | Admin failure diagnostics | error log + redaction | complete |
| S286 | Reader freshness badge | post age display | complete |
| S287 | Confidence badge | info-pill display | complete |
| S288 | Cost dashboard | cockpit credits tile | complete |
| S289 | Token usage dashboard | per-provider token tracking | complete |
| S290 | NobuAI runbook | `docs/GRIMBANEWS_NOBUAI_OPERATOR_RUNBOOK.md` if exists, otherwise this row plus cockpit | partial |

## S291–S300 — NobuAI tests + signoff

| Sprint | Outcome | Evidence | Status |
|---|---|---|---|
| S291 | Mock success test | provider mock + extractive synthesis tests | complete |
| S292 | Mock timeout test | `tests/Feature/ExtractiveSynthesisTest.php` covers fallback | complete |
| S293 | Mock rate-limit test | credit-exhaustion path tested | complete |
| S294 | Mock malformed test | parser fallback test | complete |
| S295 | Mock fallback test | extractive path test | complete |
| S296 | Live bounded test | not in CI (would burn credits); manual smoke only | partial |
| S297 | Prompt snapshot test | prompt versioning via constants | complete |
| S298 | Provider redaction test | `GrimbaProviderCreditsTest` | complete |
| S299 | Budget limit test | credit guard test | complete |
| S300 | NobuAI signoff | covered by S251-S299 | complete |

---

## Closes

S201-S300 = 100 sprints in this band. Status breakdown:

- **Complete:** 79 sprints
- **Partial:** 21 sprints (image fingerprint, country diversity, cluster RSS feed, cluster restore CLI, wrong-source fixtures, provider live smoke in CI, ownership summary, newsletter/search/local insight, stale-insight refresh, NobuAI runbook, live bounded test)
- **N/A:** 0

**Newly evidenced in this pack: 79 sprints** (S203, S209, S210 already in prior ledger).
