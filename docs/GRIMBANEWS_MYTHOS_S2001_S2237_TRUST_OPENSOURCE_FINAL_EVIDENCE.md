# Mythos S2001–S2237 — Public Trust + Ombudsman + Open Source + Reader Literacy v3 + Editorial Breadth v3 + Final Arc Evidence Pack

**Status:** evidence reconciliation (final 237-sprint band)
**Created:** 2026-05-22
**Author:** Wave HHHHHHHHHH batch close (final Mythos post-launch band — closes 2237-arc to 100% ledger coverage)
**Scope:** Converts the final 237-sprint slice of the Mythos S1001–S2237 post-launch arc — **public trust** (annual transparency report cadence), **ombudsman role + complaint workflow**, **open-source releases** (methodology repo / translator / detector / cluster engine), **community contribution flow**, **reader literacy v3** (schools / adult-education / civic-NGO partnerships), **editorial breadth v3** (under-covered regions DOM-TOM / Pacific / smaller-AU + niche-topic v2 climate / science / culture + long-form investigations) and the **final arc** (multi-decade preservation / archive cadence / end-of-Mythos retrospective) — into ledger rows pointing at the honest current state of GrimbaNews.

This pack feeds the master `Sprint Evidence Ledger` in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`. The 237 sprint IDs in S2001–S2237 now have a ledger row.

**Scaffold-honesty preamble.** The Mythos master plan explicitly flags the S1801–S2230 rows as templated scaffold (see `GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` line ~2049 "Scaffold honesty note (Wave OOOOOOOO 2026-05-20)"). The discipline-owner pass — Sara Chen for Security/Compliance, Ray for Finance, Lucy for Strategy, an as-yet-unhired ombudsman / community-manager / open-source-program-manager for the trust+OSS bands — has not happened. As a result, **the vast majority of S2001–S2237 is honest `deferred`**: not because the sprints are unimportant, but because they need a discipline-owner specification, third-party accounts (GitHub org / OSS hosting / DOI registrar / school-program partner / community moderator), legal / counsel review (ombudsman charter, complaint workflow under jurisdictional press-council rules), and post-launch operator pickup (the operational ones cannot start until prod has been live for ≥6-12 months).

**What is genuinely shipped today** that touches this band, even as a surrogate:

- **Archive primitive** — `App\Console\Commands\GrimbaArchiveVaultEvents` (`grimba:archive-vault-events`) is wired into the scheduler via `App\Support\GrimbaAutomationMonitor::$jobs['vault_events_archive']` (cadence registered in `routes/console.php`). This is the only "archive cadence" surface that currently exists end-to-end and is the surrogate cited for S2221-S2230 multi-decade-preservation rows.
- **Regions taxonomy** — `App\Ground\Regions` and `App\Scopes\GrimbaRegionScope` enumerate Pacific + Antarctica as first-class regions; ingestion does not currently have DOM-TOM-specific feeds wired, so the region taxonomy is locale-ready but the editorial roster is the missing piece.
- **Investigation/long-form keyword recognition** — `App\Services\GrimbaCategoryClassifier` (`Justice` bucket) and `App\Console\Commands\GrimbaBackfillCategory` (Justice keywords list) already recognize "investigation" / "trial" / "court ruling" / "prosecution" as Justice signals. Long-form-investigation editorial program would build on top of this classifier.
- **Methodology surfaces** — `docs/GRIMBANEWS_S301_S500_TRANSLATION_BREAKDOWN_HOMEPAGE_PACK.md` + `docs/GRIMBANEWS_S201_S300_DEDUP_CLUSTER_NOBUAI_PACK.md` + `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md` + `docs/GRIMBANEWS_LANGUAGE_TAGGING_PLAN.md` are the *internal* methodology documentation that an open-source release would publish; nothing has been extracted, license-cleared, or pushed to a public repo.
- **Niche-topic editorial categories** — `App\Services\GrimbaCategoryClassifier::CATEGORIES` includes Climate, Science, Culture buckets; the v3 expansion would be deeper per-bucket source rosters + per-bucket editorial briefs.

Everything else in S2001-S2237 needs either a discipline-owner spec (per the scaffold honesty note), a third-party account / partner contract, paid tier (no monetization shipped — gates on S1211), or a post-launch operational track that cannot start before prod cutover. The honest count is **~3 partial (with shipped surrogates), ~234 deferred**.

---

## S2001–S2020 — Public trust — transparency report annual cadence

The annual transparency report is the public-trust anchor — counts of moderation actions, takedown requests, government / police data requests, source-license challenges, correction issuances, ad-rejection counts, NobuAI-cost transparency, AB-test transparency. GrimbaNews does not have a single such report shipped today; the underlying counters (`grimba_automation_runs`, `news_sources.factuality_score` exclusion log, `GrimbaProviderCredits` daily-cap counter, `coffre/export.csv` subscriber funnel, `GrimbaVaultEvents` privacy-safe event ledger) exist but no aggregation surface emits an annual report. All `deferred` until at least one full operational year has elapsed post-launch + an editorial owner publishes the first edition.

- **S2001** — Annual transparency report — scope definition: `deferred` — scaffold per Mythos honesty note; needs editorial-owner + counsel + Vader scope sign-off before first edition.
- **S2002** — Annual transparency report — moderation-action counts: `deferred` — no moderation queue shipped (gates on S1591 moderation_queue table); raw signal today is `grimba_automation_runs` for ingest-time rejections.
- **S2003** — Annual transparency report — takedown / DMCA counts: `deferred` — no takedown intake (gates on `mailto:` legal alias + operator log); needs annual aggregation surface.
- **S2004** — Annual transparency report — government / law-enforcement data requests: `deferred` — no LE-request intake or log; needs counsel-defined intake workflow + jurisdiction-specific reporting (US, FR, EU separately).
- **S2005** — Annual transparency report — source-license challenges + outcomes: `deferred` — `news_sources.license_notes` column is the operator slot per S1030; aggregation surface deferred.
- **S2006** — Annual transparency report — corrections issued + per-source count: `deferred` — no corrections primitive (gates on dedicated `corrections` table + editorial workflow S1291).
- **S2007** — Annual transparency report — ad rejections + per-category breakdown: `deferred` — `App\Support\GrimbaAds` consent-gating hooks exist per S871 ads pack but no rejection log + annual aggregation.
- **S2008** — Annual transparency report — NobuAI cost + provider mix transparency: `partial` — `GrimbaProviderCredits` daily counters exist (used / cached / fast / bump per provider per UTC day); annual public-facing aggregation `deferred` (counters today are admin-only).
- **S2009** — Annual transparency report — A/B-test outcomes transparency: `deferred` — no A/B engine wired (S1073).
- **S2010** — Annual transparency report — methodology change log (per-year): `deferred` — internal change log is the git history; public methodology-versioning surface `deferred`.
- **S2011** — Annual transparency report — publish cadence: `deferred` — needs ≥1 full operational year + editorial-owner pickup.
- **S2012** — Annual transparency report — multi-locale publication (FR + EN + post-launch locales): `deferred` — gates on S2011 + per-locale catalogs (S1101+).
- **S2013** — Annual transparency report — open-data download bundle (CSV / JSON): `deferred` — surrogate today is `coffre/export.csv` subscriber export; transparency-data export `deferred`.
- **S2014** — Annual transparency report — third-party audit attestation: `deferred` — needs external auditor engagement (PwC / Deloitte for finance + counsel for legal + a press-council body for editorial); zero contracts today.
- **S2015** — Annual transparency report — comparison to peer outlets (NYT / Reuters / Le Monde): `deferred` — operator-side editorial framing.
- **S2016** — Annual transparency report — press coverage of the report itself (meta): `deferred` — gates on S2011 first edition.
- **S2017** — Annual transparency report — reader feedback intake: `deferred` — no feedback intake surface today; needs `/transparency/feedback` form + moderation routing.
- **S2018** — Annual transparency report — year-over-year trend page: `deferred` — gates on ≥2 editions.
- **S2019** — Annual transparency report — archive accessibility (multi-year browsing): `deferred` — gates on S2011 + at least one prior edition.
- **S2020** — Annual transparency report — launch retrospective + next-year scope: `deferred` — gates on S2011.

## S2021–S2040 — Public trust — ombudsman role + complaint workflow

The ombudsman is the public-facing accountability role — a person or small team (often a former senior journalist) who fields reader complaints, investigates editorial-process breaches, and publishes findings independent of the editor-in-chief. GrimbaNews has no such role today, no charter, no intake workflow, no investigation log. The underlying `news_sources.license_notes` column + `App\Mail\GrimbaContactReplyMail` contact-reply surrogate are the only existing primitives. All `deferred` per scaffold honesty note + needs an actual ombudsman hire.

- **S2021** — Ombudsman charter — scope + independence guarantees: `deferred` — needs counsel + editorial-board sign-off; zero charter drafted.
- **S2022** — Ombudsman appointment — first ombudsman hire: `deferred` — operator-side pickup; not on any current Iboga roster.
- **S2023** — Ombudsman intake surface — `/ombudsman` page with intake form: `deferred` — no such route; surrogate is `App\Http\Controllers\GrimbaContactController` + `/api/contact` (S006).
- **S2024** — Ombudsman intake — email alias (`ombudsman@grimbanews.com`): `deferred` — no alias provisioned; needs DNS + Acelle inbox routing.
- **S2025** — Ombudsman intake — anonymous tip channel: `deferred` — needs SecureDrop or equivalent; zero anonymous-tip infra today.
- **S2026** — Complaint triage workflow — severity rubric: `deferred` — needs ombudsman + editorial-board co-authored rubric.
- **S2027** — Complaint triage workflow — investigation log (internal): `deferred` — no log table; needs `ombudsman_investigations` schema.
- **S2028** — Complaint workflow — response SLA (e.g. 14-day initial reply, 60-day investigation close): `deferred` — operator-side SLA contract.
- **S2029** — Complaint workflow — public findings publication (per-investigation): `deferred` — gates on S2023 + editorial-policy on public-vs-private findings.
- **S2030** — Complaint workflow — anonymized-but-public log: `deferred` — gates on S2029.
- **S2031** — Ombudsman — annual report (separate from S2001 transparency report): `deferred` — separate cadence; ombudsman reports to readers, not to operator.
- **S2032** — Ombudsman — cross-locale intake (FR + EN today, more post-S1101): `deferred` — gates on S2023 + per-locale catalogs.
- **S2033** — Ombudsman — escalation to external press council (Conseil de déontologie journalistique etc.): `deferred` — needs counsel-defined per-jurisdiction routing.
- **S2034** — Ombudsman — correction-issuance authority (overrides editor-in-chief): `deferred` — needs charter clause (S2021) + corrections primitive (S2006).
- **S2035** — Ombudsman — staff-training / case-study program: `deferred` — operator-side editorial training.
- **S2036** — Ombudsman — reader-rights education page (`/your-rights` / `/vos-droits`): `deferred` — no such page today; needs counsel review per jurisdiction.
- **S2037** — Ombudsman — quarterly office-hours (public Zoom or in-person): `deferred` — operator-side cadence; not viable solo.
- **S2038** — Ombudsman — independent budget line (separate from editorial): `deferred` — needs Ray sign-off + Iboga board approval.
- **S2039** — Ombudsman — succession plan (term limits, search committee): `deferred` — gates on S2022 first hire.
- **S2040** — Ombudsman — launch retrospective: `deferred` — gates on S2021-S2039 + ≥1 year tenure.

## S2041–S2060 — Open source — methodology repo (publish bias-classification methodology under permissive license)

The methodology repo would publish — under a permissive license (MIT / Apache 2.0 / CC-BY for docs) — the bias-classification heuristics, factuality-score rubric, ownership-classification rules, cluster-merge algorithm, dedup rules, and translation-rule engine that power GrimbaNews. The *internal* documentation exists (`docs/GRIMBANEWS_S201_S300_DEDUP_CLUSTER_NOBUAI_PACK.md` + `docs/GRIMBANEWS_S301_S500_TRANSLATION_BREAKDOWN_HOMEPAGE_PACK.md` + `docs/GRIMBANEWS_S401_S450_BIAS_FACTUALITY_PACK.md` etc.) but nothing has been license-cleared, sanitized of internal-only notes, separated from operator runbooks, or pushed to a public repo. No GitHub org for OSS releases provisioned. All `deferred`.

- **S2041** — Methodology repo — scope decision (what to open-source, what to retain): `deferred` — needs Vader + Lucy + Sara Chen + counsel scope sign-off; scaffold per Mythos honesty note.
- **S2042** — Methodology repo — license selection (MIT vs Apache 2.0 vs CC-BY): `deferred` — needs counsel pass.
- **S2043** — Methodology repo — GitHub org provisioning (`github.com/grimbanews` or `github.com/iboga-ventures`): `deferred` — no public OSS org today; `darkvaderfr` is private mirror per CLAUDE.md.
- **S2044** — Methodology repo — bias-classification rubric extraction (from `app/Support/GrimbaClusterBias.php` + S401-S450 pack): `deferred` — needs license-clear + internal-notes scrub.
- **S2045** — Methodology repo — factuality-score rubric extraction (from `news_sources.factuality_score` + ingest filter): `deferred` — same.
- **S2046** — Methodology repo — ownership-classification rules (from `news_sources.ownership_type` enum + classifier): `deferred` — same.
- **S2047** — Methodology repo — cluster-merge algorithm (from `App\Services\GrimbaRssPoller::findOrFormCluster()`): `deferred` — same.
- **S2048** — Methodology repo — dedup rules (from `App\Support\GrimbaArticleDedupe` + canonical URL + title similarity): `deferred` — same.
- **S2049** — Methodology repo — translation-rule engine (from `App\Support\GrimbaTranslationRules` + `grimba_lang_rule_engine_daily_cap`): `deferred` — same.
- **S2050** — Methodology repo — README + getting-started guide: `deferred` — gates on S2041 scope decision.
- **S2051** — Methodology repo — CONTRIBUTING.md + code of conduct: `deferred` — needs community-manager owner; not hired.
- **S2052** — Methodology repo — versioning policy (semver vs date-based): `deferred` — gates on S2041.
- **S2053** — Methodology repo — DOI registration for citable methodology: `deferred` — needs Zenodo / Figshare account.
- **S2054** — Methodology repo — academic-paper companion: `deferred` — operator-side academic output; needs PI + funding.
- **S2055** — Methodology repo — translation of repo (FR + EN at minimum): `deferred` — gates on S2050.
- **S2056** — Methodology repo — issue-triage workflow: `deferred` — gates on S2043 + community-manager hire.
- **S2057** — Methodology repo — PR-review workflow: `deferred` — same.
- **S2058** — Methodology repo — release cadence (quarterly vs ad-hoc): `deferred` — gates on S2041.
- **S2059** — Methodology repo — sponsorship / GitHub Sponsors integration: `deferred` — gates on S2043 + Stripe Atlas / SponsorLink.
- **S2060** — Methodology repo — launch retrospective: `deferred` — gates on S2041-S2059.

## S2061–S2080 — Open source — translator / detector / cluster engine release

The actual code release — `App\Services\GrimbaTranslator` (NobuAI / OpenRouter / LibreTranslate driver chain), `App\Services\GrimbaLanguageDetector` (n-gram + TLD + 26-test suite), and the cluster engine (`App\Services\GrimbaRssPoller::findOrFormCluster()`) — would be the most directly reusable open-source contribution. None of these have been extracted into standalone repos, none have been license-cleared (NobuAI driver depends on private provider integrations), none have been ported to a framework-neutral form (currently Laravel-coupled). All `deferred`.

- **S2061** — Translator OSS release — repo scaffolding: `deferred` — Laravel-coupled today; needs framework-neutral port.
- **S2062** — Translator OSS release — NobuAI / OpenRouter / LibreTranslate driver split: `deferred` — needs per-driver-package separation + each license-cleared independently (NobuAI driver may stay closed).
- **S2063** — Translator OSS release — rule-engine OSS (from `App\Support\GrimbaTranslationRules`): `deferred` — needs scope decision (S2041).
- **S2064** — Translator OSS release — quality-eval harness: `deferred` — no eval harness exists internally; would need build before release.
- **S2065** — Translator OSS release — example apps / cookbook: `deferred` — gates on S2061.
- **S2066** — Detector OSS release — n-gram corpus extraction (from `App\Services\GrimbaLanguageDetector`): `deferred` — corpus is embedded constants; needs export tooling + license-cleared upstream sources.
- **S2067** — Detector OSS release — TLD heuristic table: `deferred` — small enough to embed; gates on S2066.
- **S2068** — Detector OSS release — 26-test fixture suite: `partial` — `tests/Unit/GrimbaLanguageDetectorTest.php` is the test surface (26 tests covering ES/PT-BR/DE/IT + n-gram + TLD per S1028); extraction to standalone package `deferred`.
- **S2069** — Detector OSS release — Python / JS / Rust port: `deferred` — PHP-only today; needs polyglot maintainers.
- **S2070** — Detector OSS release — benchmark page vs cld3 / fastText: `deferred` — no benchmark harness today.
- **S2071** — Cluster engine OSS release — algorithm extraction: `deferred` — tightly coupled to `posts` table + Laravel ORM; needs schema-neutral port.
- **S2072** — Cluster engine OSS release — canonical-URL normalizer (from `GrimbaArticleText::normalize()`): `partial` — `GrimbaArticleText::normalize()` (S203) is a small focused utility; cleanest OSS-able piece; release `deferred`.
- **S2073** — Cluster engine OSS release — title-similarity threshold tuning guide: `deferred` — needs published guide + tuning fixtures.
- **S2074** — Cluster engine OSS release — orphan-cluster cleanup pattern: `deferred` — Laravel-coupled; needs schema-neutral port.
- **S2075** — Cluster engine OSS release — bias-diversity scoring (from `GrimbaSourceBreakdown::countryBiasBuckets()`): `deferred` — needs framework-neutral port + license-clear.
- **S2076** — Cluster engine OSS release — confidence-score model: `deferred` — rule-based today (S1053); needs export tooling.
- **S2077** — Cluster engine OSS release — example datasets (synthetic + real): `deferred` — would need contributor-cleared real corpus or synthetic generator.
- **S2078** — Cluster engine OSS release — academic citation guide: `deferred` — gates on S2053 DOI.
- **S2079** — Cluster engine OSS release — community fork tracker: `deferred` — gates on S2043 + community-manager.
- **S2080** — Translator+detector+cluster OSS — joint launch retrospective: `deferred` — gates on S2061-S2079.

## S2081–S2100 — Open source — community contribution flow

The community-contribution flow — the policies, automation, and human reviewers that turn external pull requests into accepted methodology / code contributions — is the discipline that makes OSS sustainable. Zero infrastructure today (no DCO bot, no signed-CLA, no triage rotation, no contributor-onboarding doc). All `deferred` until S2043 + S2051 + S2056 land.

- **S2081** — Community — code of conduct (Contributor Covenant 2.1 baseline): `deferred` — no public repo today.
- **S2082** — Community — DCO (Developer Certificate of Origin) bot: `deferred` — same.
- **S2083** — Community — CLA (Contributor License Agreement) or DCO-only decision: `deferred` — needs counsel.
- **S2084** — Community — triage rotation roster: `deferred` — needs community-manager owner + ≥3 maintainers.
- **S2085** — Community — PR-review SLA (e.g. first-response in 7 days): `deferred` — operator-side SLA.
- **S2086** — Community — contributor-onboarding doc (GOOD-FIRST-ISSUE labels + dev-env setup): `deferred` — needs CONTRIBUTING.md (S2051).
- **S2087** — Community — recognition program (CONTRIBUTORS.md + monthly shout-outs): `deferred` — gates on S2043.
- **S2088** — Community — mentorship program (pair external contributor with maintainer): `deferred` — needs sustained community-manager bandwidth.
- **S2089** — Community — quarterly community call (Zoom / Jitsi / Discord stage): `deferred` — operator-side cadence.
- **S2090** — Community — Discord / Matrix / Slack channel: `deferred` — needs channel provisioning + moderator roster.
- **S2091** — Community — bug-bounty program (overlaps with S2011-S2020 security bug-bounty band): `deferred` — needs HackerOne / YesWeHack account + scope.
- **S2092** — Community — security-disclosure policy (`SECURITY.md` per RFC 9116): `partial` — `public/.well-known/security.txt` ships per S995 / GrimbaLaunchReadinessTest security-headers test; repo-level `SECURITY.md` `deferred`.
- **S2093** — Community — sponsor-recognition page (Open Collective / GitHub Sponsors): `deferred` — gates on S2059.
- **S2094** — Community — i18n translation contribution flow (Crowdin / Weblate): `deferred` — would streamline S1101+ i18n catalog ingestion; not provisioned.
- **S2095** — Community — fork-friendly architecture decision records (ADRs): `deferred` — internal ADRs do not exist as a public-facing series.
- **S2096** — Community — academic-partnership intake (research collaborators): `deferred` — operator-side academic outreach.
- **S2097** — Community — press / journalist intake (case studies on Grimba methodology): `deferred` — operator-side press relations.
- **S2098** — Community — annual community survey: `deferred` — gates on S2043 + ≥1 year of contributors.
- **S2099** — Community — anti-harassment escalation path (CoC enforcement): `deferred` — needs CoC committee (≥3 people).
- **S2100** — Community — flow launch retrospective: `deferred` — gates on S2081-S2099.

## S2101–S2120 — Reader literacy v3 — schools partnership program

The schools partnership — high schools and universities adopting GrimbaNews as a "media literacy curriculum companion" — is a multi-year community-investment track. No school contacted, no curriculum drafted, no per-school login flow, no teacher dashboard, no privacy review for COPPA / GDPR-K (children's data) compliance. All `deferred`.

- **S2101** — Schools program — scope decision + first-pilot region: `deferred` — needs partnership-program owner (not hired).
- **S2102** — Schools program — curriculum draft (media literacy + bias-analysis exercises): `deferred` — needs pedagogy partner.
- **S2103** — Schools program — per-school login flow (LMS SSO / Google Classroom / Microsoft Teams Education): `deferred` — single-tenant auth today.
- **S2104** — Schools program — teacher dashboard (assign articles, see class progress): `deferred` — no LMS surface.
- **S2105** — Schools program — student-data privacy review (COPPA / GDPR-K / Quebec Law 25): `deferred` — needs counsel per jurisdiction.
- **S2106** — Schools program — age-appropriate content filter: `deferred` — current source roster has no per-source age-rating.
- **S2107** — Schools program — French-curriculum alignment (Éducation nationale): `deferred` — operator-side pedagogy mapping.
- **S2108** — Schools program — Canadian-curriculum alignment (per-province): `deferred` — same.
- **S2109** — Schools program — US-curriculum alignment (Common Core ELA + NAMLE media-literacy standards): `deferred` — same.
- **S2110** — Schools program — IB / Cambridge alignment: `deferred` — same.
- **S2111** — Schools program — teacher-training workshops: `deferred` — needs trainer roster + travel budget.
- **S2112** — Schools program — student-essay corpus (anonymized + published with permission): `deferred` — needs IRB-equivalent review.
- **S2113** — Schools program — annual student-essay contest: `deferred` — operator-side editorial.
- **S2114** — Schools program — alumni network: `deferred` — gates on ≥1 year of student cohorts.
- **S2115** — Schools program — pricing decision (free for schools? sponsored seats?): `deferred` — needs Ray unit-economics review.
- **S2116** — Schools program — case studies (per-school): `deferred` — gates on S2101 first partnership.
- **S2117** — Schools program — research-paper coauthorship with partner schools: `deferred` — operator-side academic output.
- **S2118** — Schools program — accessibility for special-needs classrooms: `deferred` — needs A11y v3 (per S1571-S1580 deferred set).
- **S2119** — Schools program — multilingual deployment (FR + EN + post-launch locales): `deferred` — gates on S1101+ catalogs.
- **S2120** — Schools program — launch retrospective: `deferred` — gates on S2101-S2119 + ≥1 academic year.

## S2121–S2140 — Reader literacy v3 — adult-education program

Adult-education — public libraries, community centers, citizenship-prep programs, ESL programs — is a complementary track. Same shape: no partner contacted, no curriculum, no per-program enrollment flow. All `deferred`.

- **S2121** — Adult-ed program — scope decision + first-pilot region: `deferred` — needs partnership-program owner.
- **S2122** — Adult-ed program — curriculum draft (news-consumption hygiene + bias-spotting): `deferred` — needs adult-pedagogy partner.
- **S2123** — Adult-ed program — public-library partnership (BAnQ / Bibliothèque de Lyon / NYPL): `deferred` — operator-side outreach.
- **S2124** — Adult-ed program — community-center partnership (YMCA / Maison de quartier): `deferred` — same.
- **S2125** — Adult-ed program — citizenship-prep partnership (FR naturalisation / Canada citizenship-test / US USCIS): `deferred` — same.
- **S2126** — Adult-ed program — ESL / FSL classroom integration: `deferred` — surrogate is FR ↔ EN parity per S301; per-curriculum integration `deferred`.
- **S2127** — Adult-ed program — print-handout assets (offline-classroom mode): `deferred` — no print-CSS layout shipped.
- **S2128** — Adult-ed program — facilitator-training workshops: `deferred` — needs trainer roster.
- **S2129** — Adult-ed program — accessibility for low-literacy learners (audio mode): `deferred` — no TTS layer.
- **S2130** — Adult-ed program — pricing decision (free for libraries?): `deferred` — needs Ray review.
- **S2131** — Adult-ed program — per-program enrollment flow: `deferred` — single-tenant auth today.
- **S2132** — Adult-ed program — case studies: `deferred` — gates on S2121 first partnership.
- **S2133** — Adult-ed program — diaspora-community partnership (CFA / Maison de l'Afrique / Centro Sefarad-Israel): `deferred` — overlaps Afrique-edition editorial focus.
- **S2134** — Adult-ed program — refugee-resettlement-org partnership (UNHCR / IRC / La Cimade): `partial` — La Cimade + UNHCR feeds already integrated via `GrimbaSeedImmigrationSources` per S1024 source-roster sub-Saharan band; explicit partnership program `deferred`.
- **S2135** — Adult-ed program — multilingual deployment: `deferred` — gates on S1101+ catalogs.
- **S2136** — Adult-ed program — privacy-by-default (anonymous learners): `partial` — `GrimbaVaultEvents` is privacy-safe (ip_hash) per S1010; explicit anonymous-learner mode `deferred`.
- **S2137** — Adult-ed program — group-progress dashboard (facilitator surface): `deferred` — no LMS surface.
- **S2138** — Adult-ed program — feedback intake from learners: `deferred` — no feedback surface today.
- **S2139** — Adult-ed program — alumni-mentorship channel: `deferred` — operator-side community.
- **S2140** — Adult-ed program — launch retrospective: `deferred` — gates on S2121-S2139.

## S2141–S2160 — Reader literacy v3 — civic / NGO partnership

Civic / NGO partnerships — Reporters Without Borders, RSF, Conseil de presse, Trust Project, NewsGuard, AllSides — would give external validation of methodology + reach into existing media-literacy ecosystems. None engaged today. All `deferred`.

- **S2141** — Civic-NGO program — scope decision: `deferred` — scaffold per Mythos honesty note; needs Lucy + Vader scope decision.
- **S2142** — RSF (Reporters Without Borders) partnership intake: `deferred` — no outreach.
- **S2143** — Conseil de déontologie journalistique partnership: `deferred` — same.
- **S2144** — Trust Project (`thetrustproject.org`) trust-indicator adoption: `deferred` — 8 trust indicators (Best Practices / Author / Type of Work / Citations / Methods / Locally Sourced / Diverse Voices / Actionable Feedback) — none implemented as machine-readable schema.
- **S2145** — NewsGuard rating-engagement: `deferred` — external rating service; engagement is operator-side.
- **S2146** — AllSides bias-rating cross-validation: `deferred` — internal `GrimbaClusterBias` could be cross-validated against AllSides; cross-validation harness `deferred`.
- **S2147** — Médias en Seine / SOJC / academic-conference participation: `deferred` — operator-side presence.
- **S2148** — IFCN (International Fact-Checking Network) signatory pursuit: `deferred` — needs Code of Principles compliance audit; not started.
- **S2149** — JournalismAI / Polis-LSE research partnership: `deferred` — operator-side academic outreach.
- **S2150** — Knight Foundation / Craig Newmark Philanthropies grant pursuit: `deferred` — operator-side fundraising.
- **S2151** — Civil-society advocacy coalition (electoral integrity orgs, etc.): `deferred` — operator-side.
- **S2152** — NGO data-license agreements (use Grimba data in their reports): `deferred` — needs S1181 public API v2.
- **S2153** — Coverage of NGO-published reports (editorial commitment): `deferred` — operator-side editorial.
- **S2154** — Joint events with NGO partners: `deferred` — gates on first partnership.
- **S2155** — Joint research publications: `deferred` — gates on S2149 academic partnership.
- **S2156** — Civic-NGO case studies (per-partner): `deferred` — gates on S2141 first.
- **S2157** — Cross-locale NGO partnerships (per-region): `deferred` — gates on S2141.
- **S2158** — Annual civic-NGO partner summit: `deferred` — operator-side cadence.
- **S2159** — Renewal / retention metrics (per-partner): `deferred` — gates on partnerships existing.
- **S2160** — Civic-NGO launch retrospective: `deferred` — gates on S2141-S2159.

## S2161–S2180 — Editorial breadth v3 — under-covered region expansion (DOM-TOM, Pacific, smaller AU countries)

The editorial-breadth v3 band targets regions the global newscycle systematically under-covers — French DOM-TOM (Guadeloupe, Martinique, Guyane, Mayotte, Réunion, Polynésie, Nouvelle-Calédonie, Saint-Pierre-et-Miquelon, Wallis-et-Futuna, Saint-Barthélemy, Saint-Martin), Pacific island states (Fiji, Samoa, Tonga, Vanuatu, Solomon Islands, Papua New Guinea), smaller African Union member states (Comoros, Lesotho, eSwatini, São Tomé, Cabo Verde, Eritrea, Djibouti, Burundi). Region taxonomy is locale-ready (`App\Ground\Regions` already enumerates Pacific + Antarctica); the editorial roster is the missing piece. All `deferred` to operator-side editorial pickup.

- **S2161** — DOM-TOM source-roster expansion — scope: `deferred` — operator-side editorial pickup; surrogate is `RssFeedsSeeder` + `grimba:classify-sources` cron from S1021 EU-east band.
- **S2162** — Guadeloupe / Martinique sources (RCI / Outremers360 / France Antilles): `deferred` — needs feed-URL research + license review.
- **S2163** — Guyane sources (Guyane la 1ère / France-Guyane): `deferred` — same.
- **S2164** — Mayotte / Réunion sources (Mayotte la 1ère / Linfo.re / Clicanoo): `deferred` — same.
- **S2165** — Polynésie / Nouvelle-Calédonie / Wallis-et-Futuna sources (Tahiti Infos / Nouvelle-Calédonie la 1ère): `deferred` — same.
- **S2166** — Saint-Pierre-et-Miquelon / Saint-Barthélemy / Saint-Martin sources: `deferred` — same.
- **S2167** — Pacific islands sources — Fiji / Samoa / Tonga / Vanuatu (Pacific Beat / RNZ Pacific): `deferred` — same.
- **S2168** — Pacific islands sources — Solomon Islands / Papua New Guinea (PNG Post-Courier / Solomon Star): `deferred` — same.
- **S2169** — Cabo Verde / São Tomé / Comoros sources: `deferred` — same.
- **S2170** — Lesotho / eSwatini / Eritrea / Djibouti / Burundi sources: `deferred` — same.
- **S2171** — Under-covered region taxonomy v2 (per-country buckets): `partial` — `App\Ground\Regions` lists Pacific + Antarctica as first-class regions; per-country DOM-TOM bucket `deferred` (current taxonomy is region-level not country-level for these territories).
- **S2172** — Per-region NobuAI prompt tuning (local context): `deferred` — single global prompt today (S1082); per-edition prompt `deferred`.
- **S2173** — Per-region credibility-score baseline (local-source factuality): `deferred` — needs operator-side editorial calibration per region.
- **S2174** — Per-region editorial-policy review: `deferred` — operator-side.
- **S2175** — Per-region launch comms: `deferred` — gates on S2161-S2173.
- **S2176** — Per-region reader-feedback intake: `deferred` — no feedback surface today.
- **S2177** — Per-region partnership with local newsrooms: `deferred` — operator-side editorial outreach.
- **S2178** — Per-region under-covered-story tracker (proactive coverage): `deferred` — needs editorial-workflow S1291.
- **S2179** — Per-region annual coverage-density report: `deferred` — gates on S2001 transparency report + per-region counters.
- **S2180** — Under-covered-region program launch retrospective: `deferred` — gates on S2161-S2179.

## S2181–S2200 — Editorial breadth v3 — niche-topic expansion (climate, science, culture v2)

The niche-topic v2 band — deeper per-bucket source rosters + per-bucket editorial briefs for climate, science, culture, plus new buckets (technology v2 long-form, health v2 long-form, sports v2 international). Current `GrimbaCategoryClassifier::CATEGORIES` ships Climate / Science / Culture but at v1 depth (keyword classifier + basic source pool per S1033). All `deferred` to operator-side editorial pickup.

- **S2181** — Niche-topic v2 — scope decision (which buckets, how deep): `deferred` — operator-side editorial.
- **S2182** — Climate v2 — deep source-roster expansion (Carbon Brief / Inside Climate News / Reporterre / Vert / Mongabay): `deferred` — operator-side pickup; surrogate is `grimba:seed-thin-category-sources` (per S1024).
- **S2183** — Climate v2 — per-COP coverage program: `deferred` — operator-side editorial commitment.
- **S2184** — Climate v2 — methodology coverage (climate-science vs climate-policy explainer): `deferred` — operator-side editorial.
- **S2185** — Climate v2 — IPCC-report coverage playbook: `deferred` — operator-side.
- **S2186** — Science v2 — preprint-server integration (arXiv / bioRxiv / medRxiv): `deferred` — needs ingest adapter; not RSS-native.
- **S2187** — Science v2 — peer-reviewed-journal coverage (Nature / Science / The Lancet briefings): `deferred` — operator-side editorial.
- **S2188** — Science v2 — science-misinfo fact-check track: `deferred` — overlaps S1596 misinformation flag.
- **S2189** — Science v2 — university press-release source roster (EurekAlert / AlphaGalileo): `deferred` — needs feed-URL research + license review.
- **S2190** — Science v2 — per-discipline buckets (physics / biology / climate-science / AI-ML / etc.): `deferred` — current Science bucket is flat.
- **S2191** — Culture v2 — books / film / music / theater per-sub-bucket: `deferred` — current Culture bucket is flat.
- **S2192** — Culture v2 — francophone-cultural-events coverage (Avignon / Festival du Film de la Réunion etc.): `deferred` — operator-side editorial commitment.
- **S2193** — Culture v2 — diaspora-cultural coverage (African diaspora, Caribbean diaspora): `deferred` — overlaps S2133 adult-ed diaspora-community.
- **S2194** — Technology v2 long-form — explainer track (vs newsy short): `deferred` — operator-side editorial.
- **S2195** — Health v2 long-form — public-health track (vs disease-update short): `deferred` — operator-side editorial.
- **S2196** — Sports v2 international — beyond football: `deferred` — operator-side editorial.
- **S2197** — Niche-topic per-bucket newsletter (overlaps S1036 per-topic newsletter): `deferred` — gates on S1271+ newsletter v2.
- **S2198** — Niche-topic per-bucket landing page (deeper than `/categorie/{slug}`): `deferred` — current category landing is per-classifier-bucket.
- **S2199** — Niche-topic v2 coverage-density tracker: `deferred` — gates on S2179 per-region tracker pattern.
- **S2200** — Niche-topic v2 launch retrospective: `deferred` — gates on S2181-S2199.

## S2201–S2220 — Editorial breadth v3 — long-form investigations

Long-form investigations — multi-week reporting projects with multiple sources, primary documents, data analysis, and 3000+-word output — are the high-end of editorial work. GrimbaNews is currently aggregation + clustering; there is no in-house long-form investigative output. `App\Services\GrimbaCategoryClassifier` Justice bucket already recognizes investigation keywords. All `deferred` to operator-side editorial pickup + needs hiring reporters.

- **S2201** — Long-form investigations — scope decision + first-investigation pick: `deferred` — operator-side editorial.
- **S2202** — Long-form investigations — investigative-reporter hire (first hire): `deferred` — not on current Iboga roster.
- **S2203** — Long-form investigations — multi-source intake (primary docs, FOIA, leaks): `deferred` — needs SecureDrop / OnionShare; no anonymous-tip infra (S2025).
- **S2204** — Long-form investigations — FOIA / loi sur l'accès à l'information template library: `deferred` — operator-side legal tooling.
- **S2205** — Long-form investigations — data-analysis pipeline (CSV / Pandas / DuckDB): `deferred` — operator-side tooling.
- **S2206** — Long-form investigations — collaborative editing surface (Google Docs / Notion / CoWriter): `deferred` — operator-side.
- **S2207** — Long-form investigations — fact-check workflow (per-investigation): `deferred` — overlaps S2148 IFCN signatory pursuit.
- **S2208** — Long-form investigations — counsel review per-investigation (pre-publication): `deferred` — needs retained press counsel.
- **S2209** — Long-form investigations — long-form layout template (>3000 words, multi-image, pull-quote): `deferred` — current article layout is standard reader (`partials/post-hero-img.blade.php`).
- **S2210** — Long-form investigations — multi-locale publication (FR + EN simultaneous): `partial` — translation pipeline ready (`grimba:translate-by-rule` per S1046); long-form-specific quality pass `deferred`.
- **S2211** — Long-form investigations — companion data publication (raw data + analysis notebook): `deferred` — overlaps S2013 transparency-data export.
- **S2212** — Long-form investigations — companion podcast / video: `deferred` — no podcast / video pipeline.
- **S2213** — Long-form investigations — press-release distribution to peer outlets: `deferred` — operator-side comms.
- **S2214** — Long-form investigations — awards-submission cadence (Pulitzer / Albert-Londres / European Press Prize): `deferred` — operator-side recognition.
- **S2215** — Long-form investigations — reader-impact tracking (downloads / shares / policy outcome): `deferred` — needs `GrimbaVaultEvents` extension + outcome-log column.
- **S2216** — Long-form investigations — investigation-archive (separate from regular archive): `deferred` — gates on first investigation.
- **S2217** — Long-form investigations — collaborative investigations with peer outlets (ICIJ-style): `deferred` — operator-side editorial partnerships.
- **S2218** — Long-form investigations — pricing decision (premium tier? free?): `deferred` — gates on S1211 monetization.
- **S2219** — Long-form investigations — annual investigations review: `deferred` — gates on ≥1 year of investigations.
- **S2220** — Long-form investigations launch retrospective: `deferred` — gates on S2201-S2219.

## S2221–S2237 — Final arc — multi-decade preservation, archive cadence, end-of-Mythos retrospective

The final arc anchors GrimbaNews as a multi-decade reference — the archive cadence has to survive maintainer turnover, hosting changes, format obsolescence, and changing legal regimes. `App\Console\Commands\GrimbaArchiveVaultEvents` (`grimba:archive-vault-events`) is the only existing archive-cadence primitive (wired into `App\Support\GrimbaAutomationMonitor::$jobs['vault_events_archive']` + `routes/console.php`). Everything else — long-term storage, IIPC / Internet Archive integration, format migrations, legal-deposit compliance, end-of-Mythos retro — is `deferred` to operator-side multi-decade governance.

- **S2221** — Multi-decade preservation — scope decision + retention horizon (10y / 25y / indefinite): `deferred` — needs Vader + counsel + Ray cost review; scaffold per Mythos honesty note.
- **S2222** — Multi-decade preservation — Internet Archive Wayback partnership (`save.now/archive.org`): `deferred` — free service; needs operator-side `archive.org` SPN submission cadence.
- **S2223** — Multi-decade preservation — IIPC (International Internet Preservation Consortium) membership: `deferred` — paid membership; needs Ray review.
- **S2224** — Multi-decade preservation — BnF / BAnQ legal-deposit registration (FR + Québec): `deferred` — legal-deposit is mandatory for FR publishers above a threshold; needs counsel.
- **S2225** — Multi-decade preservation — Library of Congress NDIIPP registration (US): `deferred` — operator-side outreach.
- **S2226** — Archive cadence — daily-DB-dump retention policy: `partial` — `App\Support\GrimbaDatabaseBackups` + `grimba:verify-backups` cron + restore-smoke per S965; long-term archival tier (offsite-encrypted, multi-region) `deferred` per S945 honest deferral.
- **S2227** — Archive cadence — vault-events archive cadence: `partial` — `App\Console\Commands\GrimbaArchiveVaultEvents` (`grimba:archive-vault-events`) wired into `GrimbaAutomationMonitor::$jobs['vault_events_archive']` + `routes/console.php` scheduler; long-term storage tier + multi-decade retention policy `deferred`.
- **S2228** — Archive cadence — release-evidence prune (30-day rolling) — already shipped per S999: `partial` — `App\Console\Commands\GrimbaPruneReleaseEvidence` keeps 30-day window; archival-tier for older evidence (vs prune-and-forget) `deferred`.
- **S2229** — Archive cadence — image-asset preservation (per-article hero images): `deferred` — current image-storage policy retains hero URLs but not local copies; preservation copy `deferred`.
- **S2230** — Archive cadence — translation-archive (preserve per-article translation history): `deferred` — current schema overwrites translations; per-version history `deferred`.
- **S2231** — Iboga-wide reconciliation (cross-product preservation policy alignment): `deferred` — operator-side Iboga Ventures governance; needs Sara Chen + Larry Ellison.
- **S2232** — GrimbaNews maturity audit (post-Mythos full-stack audit): `deferred` — gates on prod ≥2 years uptime + S2051 audit-readiness band.
- **S2233** — GrimbaNews exit / expansion criteria (when to spin off as separate Iboga entity?): `deferred` — operator-side strategic decision; needs Vader + Lucy.
- **S2234** — GrimbaNews 5-year vision update (post-Mythos refresh of original product brief): `deferred` — gates on ≥5 years operational data.
- **S2235** — GrimbaNews founder retrospective (Vader written reflection on Mythos arc): `deferred` — operator-side founder pickup; cannot generate.
- **S2236** — GrimbaNews Mythos master fleet final closure (move all S001-S2237 ledger rows to `closed` status with final evidence): `deferred` — gates on S2237; this is the meta-row that closes the Mythos arc itself.
- **S2237** — GrimbaNews S2237 ledger signoff (final operator + audit-panel sign-off that the 2237-arc is complete and the ledger is authoritative): `deferred` — gates on S2236 + Zen / Echo / Mnemo audit panel + Vader written signoff. Until then this row records that the ledger has been built out to 100% row coverage but the underlying program is mostly future work.

---

## Summary

All 237 sprint IDs in S2001–S2237 now carry a ledger row in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`.

- **Complete (0 sprints):** none. Every row in this band depends on at least one of: a discipline-owner spec that has not happened, a third-party account / partner contract, a paid tier (no monetization), or a post-launch operational track that cannot start before prod cutover.
- **Partial (10 sprints):** S2008 (NobuAI cost transparency — `GrimbaProviderCredits` counter exists, public-facing aggregation deferred), S2068 (detector test fixture — `GrimbaLanguageDetectorTest` 26-test suite exists, OSS extraction deferred), S2072 (cluster-engine OSS — `GrimbaArticleText::normalize()` is the cleanest OSS-able utility, release deferred), S2092 (community security disclosure — `security.txt` ships, repo-level `SECURITY.md` deferred), S2134 (refugee-resettlement-org partnership — La Cimade + UNHCR feeds integrated via S1024, partnership program deferred), S2136 (anonymous-learner privacy — `GrimbaVaultEvents` privacy-safe primitive exists), S2171 (region taxonomy v2 — `App\Ground\Regions` lists Pacific + Antarctica, per-country DOM-TOM bucket deferred), S2210 (long-form multi-locale — translation pipeline ready, long-form quality pass deferred), S2226 (DB-dump retention — `GrimbaDatabaseBackups` shipped, long-term tier deferred), S2227 (vault-events archive — `grimba:archive-vault-events` wired into scheduler, long-term retention deferred), S2228 (release-evidence prune — `GrimbaPruneReleaseEvidence` 30-day rolling shipped, archival-tier for older evidence deferred).
- **Deferred (226 sprints):** Everything else. The reasons cluster into 6 categories:
  1. **Scaffold per Mythos honesty note** — every row labeled as scope decision, charter, first-pilot, first hire, etc. (the planning-paper rows that need a discipline-owner pass before they can be executed).
  2. **Post-launch operator pickup** — operational cadences that cannot start before prod cutover + ≥1-2 years of operational data (S2011 transparency-report cadence, S2031 ombudsman annual, S2098 community survey, S2120 schools-program retro, S2140 adult-ed retro, S2179 per-region coverage report, S2219 investigations annual, S2232 maturity audit, S2233 exit / expansion, S2234 5-year vision, S2235 founder retro).
  3. **Needs paid tier (S1211 monetization gate)** — S2115 schools pricing, S2130 adult-ed pricing, S2218 long-form pricing, S2223 IIPC paid membership.
  4. **Needs third-party account / partner contract** — GitHub OSS org (S2043), DOI registrar Zenodo (S2053), HackerOne / YesWeHack (S2091), SecureDrop (S2025, S2203), LMS SSO (S2103), counsel-retained (S2208 press counsel, S2105 student-data privacy review, S2208 ombudsman charter counsel), IFCN signatory (S2148), Trust Project schema (S2144), AllSides cross-validation (S2146), BnF / BAnQ / Library of Congress legal-deposit (S2224, S2225).
  5. **Needs new hire not on current Iboga roster** — community-manager (S2051, S2056), ombudsman (S2022), pedagogy partner (S2102, S2122), investigative reporter (S2202), partnership-program owner (S2101, S2121, S2141).
  6. **Needs published methodology repo** — every S2061-S2080 OSS-release row depends on S2041 scope decision + S2042 license selection + S2043 GitHub org provisioning.

The honest read: **0% of the S2001-S2237 band is shipped today, ~4% has a server-side surrogate, ~96% is honest deferred per the Mythos scaffold honesty note**. This matches the master plan's own preamble — the macro-bands (public trust, OSS, reader literacy, editorial breadth, multi-decade preservation) are real and necessary for a publication operating at scale, but the per-row decomposition is template scaffold that needs a discipline-owner pass before it becomes an executable sprint contract.

The valuable evidence is that the **substrate is in place**: regions taxonomy enumerates Pacific + Antarctica; archive primitive (`grimba:archive-vault-events`) is wired into the scheduler; backup verification + restore smoke exists; the bias / factuality / ownership / cluster / dedup / detector internal documentation is written and could be license-cleared if Vader green-lights the OSS track; privacy-safe event ledger respects PII; FR ↔ EN translation pipeline is ready for long-form multi-locale publication; security disclosure file ships. Each deferred row drops into a working foundation the moment the missing discipline-owner spec, hire, third-party account, or paid tier ships.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (Sprint Evidence Ledger section, new rows for S2001-S2237)
- Master plan scaffold honesty note: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` line ~2049 (Wave OOOOOOOO 2026-05-20)
- Prior packs: `docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md`, `docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md`, `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md`, `docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md`
- Sister-agent packs (in flight as of this batch): S1601-S1800 + S1801-S2000 (forthcoming)
- Methodology source documents (internal — would need license-clear for S2041 OSS scope): `docs/GRIMBANEWS_S201_S300_DEDUP_CLUSTER_NOBUAI_PACK.md`, `docs/GRIMBANEWS_S301_S500_TRANSLATION_BREAKDOWN_HOMEPAGE_PACK.md`, `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`, `docs/GRIMBANEWS_LANGUAGE_TAGGING_PLAN.md`, `docs/GRIMBANEWS_LANGUAGE_SURFACING_AND_AUTO_TRANSLATE_PLAN.md`
- Existing surrogate code surfaces:
  - Archive cadence: `app/Console/Commands/GrimbaArchiveVaultEvents.php`, `app/Console/Commands/GrimbaPruneReleaseEvidence.php`, `app/Support/GrimbaAutomationMonitor.php` (jobs registry), `routes/console.php`
  - Backups: `app/Support/GrimbaDatabaseBackups.php`, `app/Console/Commands/VerifyBackupsCommand.php` (per S965)
  - Region taxonomy: `app/Ground/Regions.php`, `app/Scopes/GrimbaRegionScope.php`
  - Investigation classifier: `app/Services/GrimbaCategoryClassifier.php` (Justice bucket), `app/Console/Commands/GrimbaBackfillCategory.php` (Justice keywords)
  - Refugee-org feeds (S2134 partial): `app/Console/Commands/GrimbaSeedImmigrationSources.php` (La Cimade, UNHCR)
  - Cost-transparency surrogate (S2008 partial): `app/Support/GrimbaProviderCredits.php`
  - Detector test fixtures (S2068 partial): `tests/Unit/GrimbaLanguageDetectorTest.php`
  - URL-normalizer (S2072 cleanest OSS-able utility): `app/Support/GrimbaArticleText.php::normalize()`
  - Privacy-safe event ledger (S2136 partial): `app/Support/GrimbaVaultEvents.php`
  - Security disclosure (S2092 partial): `public/.well-known/security.txt`
- Honest-deferral cousins in prior bands referenced by this pack:
  - S939 / S942 / S945 / S946 / S947 — live composer audit / secret rotation / offsite encrypted backup / deploy-key review / npm audit
  - S950 / S965 / S991 / S993 / S994 / S998 / S1000 — live production gates
  - S1030 — source legal coverage audit (per-source license review)
  - S1211 — monetization (paid tier) gate
  - S1271-S1290 — newsletter v2 gate
  - S1291-S1300 — editorial workflow gate
  - S1591 — moderation queue
  - S1599 — trust & safety transparency report
  - S1671 — transparency band (sister-agent S1601-S1800 pack)
