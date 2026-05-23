# GrimbaNews — Open Source Methodology Scope (v0 proposal)

**Status:** scope proposal v0 (decisions identified; Vader sign-off pending)
**Owner:** Vader + Lucy Leai (Strategy) + Sara Chen (CISO) + counsel
**Walks:** Mythos S2041 (methodology repo — scope decision) deferred → partial
**Gating dependency:** Vader sign-off as final decider on what becomes OSS. License selection (S2042) + GitHub org provisioning (S2043) follow.

## Why this exists

S2041 was honest-deferred per the Mythos scaffold honesty note as "needs Vader + Lucy + Sara Chen + counsel scope sign-off." The scope decision itself is operator-side strategic work — proposing the scope and putting the question on the table is the partial walk. Vader's signoff promotes this to complete.

## Strategic frame

Open-sourcing methodology has two strategic axes:

1. **Trust dividend** — readers / partners trust a publication more when methodology is auditable. Plays to GrimbaNews' brand positioning around bias transparency.
2. **Competitive cost** — if a methodology is the moat, OSS dilutes the moat. If the moat is editorial execution + brand, OSS strengthens the moat.

For GrimbaNews, the moat is brand + editorial execution, not the bias-classification heuristic. Releasing the heuristic strengthens trust without weakening competitive position. Initial scope recommendation: **lean toward open**.

## Proposed scope (what's in, what's out)

### IN scope — release as OSS

| Asset | License recommendation | Rationale |
|---|---|---|
| Bias-classification rubric (heuristic + thresholds, NOT classifier weights or training data) | CC-BY 4.0 for docs | Readers should be able to verify how we judge bias |
| Factuality-score rubric (how thresholds are set, what factuality_score < N means) | CC-BY 4.0 | Same |
| Ownership-classification taxonomy (state / nonprofit / philanthropy / peer-fund / commercial / unknown) | CC-BY 4.0 | Same |
| Cluster-merge algorithm (canonical-URL + title-similarity logic, NOT the training data) | MIT (code) | Useful primitive for other news-aggregation projects |
| Dedup rules (canonical-URL normalizer + similarity thresholds) | MIT (code) | Same |
| Language detector (`GrimbaLanguageDetector` n-gram + TLD logic) | MIT (code) + CC-BY for the n-gram corpus | Genuinely useful; small standalone library |
| Translation rule engine (`GrimbaTranslationRules`) | MIT (code) | Same |
| URL canonical normalizer (`GrimbaArticleText::normalize()`) | MIT (code) | Smallest, cleanest, easiest first release |
| Editorial category taxonomy (the 15-category list + keyword maps) | CC-BY 4.0 | No moat to protect; helps interop |

### OUT of scope — keep proprietary or internal

| Asset | Rationale |
|---|---|
| NobuAI provider routing + failover chain | Vendor-proprietary (per `feedback_nobuai_model_branding.md` — provider identities are private) |
| NobuAI prompt templates (`GrimbaNobuAiPrompts`) | Prompt engineering moat |
| Internal source-credibility scores (per-source `factuality_score`, `bias_rating`, `credibility_score` values) | Editorial judgment — releasing creates legal liability + invites manipulation |
| Member / subscriber data (obviously) | PII |
| Operational runbooks (`docs/GRIMBANEWS_PROD_*.md`, IR runbook, etc.) | Operational security |
| `.env` keys / secret material | obviously |
| Cluster engine training data (if/when we have any) | Source-licensed |
| Translation cache | Vendor-licensed |

## Decisions on the table

1. **License choice for code:** MIT vs Apache 2.0. **Proposal: MIT.** Simpler, no patent grant clauses, easier for academic reuse. Apache 2.0 if a contributor demands it.

2. **License choice for docs/rubrics:** CC-BY 4.0 vs CC-BY-SA 4.0. **Proposal: CC-BY 4.0.** Allows commercial reuse without copyleft contagion.

3. **GitHub org name:** `github.com/grimbanews` vs `github.com/iboga-ventures`. **Proposal: `github.com/iboga-ventures`** as parent (covers future Iboga product OSS releases too). Each product = its own repo under the org.

4. **Repo naming:** `grimbanews-methodology`, `grimbanews-translator`, `grimbanews-detector`, `grimbanews-cluster-engine`. Or one umbrella `grimbanews-toolkit` mono-repo. **Proposal: separate repos** — easier to license-clear independently; allows independent versioning.

5. **Versioning policy:** semver vs date-based. **Proposal: semver for code repos, date-based for methodology docs.** Code consumers expect semver; docs are inherently dated.

6. **Contribution policy:** Accept community PRs vs read-only mirror. **Proposal: accept PRs** for code repos (with DCO per S2082), **read-only** for methodology docs (changes go through internal editorial process first, then mirror).

7. **Sponsorship:** GitHub Sponsors / Open Collective. **Proposal: defer until community traction visible.** Don't open a fundraiser without value-delivery.

8. **First release scope:** **Proposal: ship URL canonical normalizer first.** Smallest, cleanest, easiest to extract — proves the muscle of OSS-release process. Then language detector (it has tests already). Then expand.

## Sequencing

Phase 1 (first 30 days post-sign-off):
- License selection + GitHub org provisioning (S2042 + S2043).
- First release: URL canonical normalizer (S2072 partial → ship).
- README + CONTRIBUTING + LICENSE per repo.

Phase 2 (months 2-3):
- Language detector release with existing 26-test fixture (S2068).
- Methodology rubrics (bias + factuality + ownership) as docs in `grimbanews-methodology`.

Phase 3 (months 4-6):
- Translation rule engine.
- Cluster engine algorithm (extracted, framework-neutral).

Phase 4 (post-DOI registration S2053):
- Academic-paper companion.
- DOI for citable methodology.

## Decisions Vader must make

- [ ] Approve "lean toward open" frame.
- [ ] Approve MIT (code) + CC-BY 4.0 (docs) license combo.
- [ ] Approve `github.com/iboga-ventures` as the OSS org.
- [ ] Approve URL canonical normalizer as Phase 1 first release.
- [ ] Authorize spend on community-manager hire when traction warrants (S2051, S2056 dependency).

Counsel review topics:
- License compatibility with CodeCanyon-derived Botble fork (per `feedback_codecanyon_license_vader_call.md` — fork is allowed, but original Botble code stays out of any OSS repo).
- DCO vs CLA decision (S2083).
- Per-jurisdiction export-control compliance (n-gram corpora are unlikely to trigger but check).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2041 row; gates for S2042-S2080)
- Existing code surfaces eligible for release:
  - `app/Support/GrimbaArticleText.php::normalize()` (Phase 1 candidate)
  - `app/Services/GrimbaLanguageDetector.php` + `tests/Unit/GrimbaLanguageDetectorTest.php` (Phase 2)
  - `app/Support/GrimbaTranslationRules.php` (Phase 3)
  - `app/Services/GrimbaRssPoller.php::findOrFormCluster()` (Phase 3)
- CodeCanyon policy (license-clear constraint): `~/.claude/projects/-Users-vb-kaizen/memory/feedback_codecanyon_license_vader_call.md`
- NobuAI branding policy (provider identity stays private): `~/.claude/projects/-Users-vb-kaizen/memory/feedback_nobuai_model_branding.md`
- Methodology source docs (extraction inputs): `docs/GRIMBANEWS_S201_S300_DEDUP_CLUSTER_NOBUAI_PACK.md`, `docs/GRIMBANEWS_S301_S500_TRANSLATION_BREAKDOWN_HOMEPAGE_PACK.md`, `docs/GRIMBANEWS_LANGUAGE_TAGGING_PLAN.md`
