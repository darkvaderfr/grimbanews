# GrimbaNews — Ombudsman Charter (v0 draft)

**Status:** charter v0 (draft; awaiting counsel review + editorial board ratification + first ombudsman hire)
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO) + retained press counsel
**Walks:** Mythos S2021 (ombudsman charter — scope + independence) deferred → partial
**Gating dependency:** Ombudsman hire (S2022) + retained press counsel for charter clauses (S2033 jurisdictional press-council routing). Charter draft itself is operator-side and is the precondition for those gates.

## Why this exists

S2021 was honest-deferred as "needs counsel + editorial-board sign-off; zero charter drafted." Drafting the charter is the operator-side step that has to happen first — counsel and editorial board need something to react to. This document is the v0 charter awaiting iteration.

## 1. Purpose

The Ombudsman exists to provide an independent reader-accountability surface for GrimbaNews. The Ombudsman receives reader complaints about editorial process, investigates breaches of editorial policy, and publishes findings independent of the editor-in-chief / operator.

The role is a public commitment: readers know there is a human (not a form-bot) whose job is to listen, investigate, and publish.

## 2. Authority

The Ombudsman has:

- Authority to receive complaints through any documented intake channel.
- Authority to investigate any editorial decision, sourcing choice, fact-check failure, correction, or process breach.
- Authority to access internal records, draft history, source provenance, and editorial deliberations relevant to investigations.
- Authority to publish findings on `/ombudsman` (S2023 surface — deferred), independent of editor-in-chief approval.
- Authority to recommend corrections / retractions.
- **No authority** to set editorial direction, hire/fire staff, or unilaterally publish content.

## 3. Independence guarantees

- **Reporting line:** Ombudsman reports administratively to the operator (Vader) and functionally to readers. Does **not** report to the editor-in-chief.
- **Term:** 3 years, renewable once (max 6 years total). Single-term safeguards against tenure capture.
- **Removal:** Only for documented misconduct (defined per S2033 jurisdictional press-council standards), not for inconvenient findings.
- **Budget:** Independent budget line per S2038 (gates on Ray sign-off + Iboga board approval).
- **Communications:** Findings publication cannot be blocked by editor-in-chief or operator. Operator may issue separate response, but cannot prevent publication.

## 4. Scope

In-scope topics:

- Sourcing decisions (did we cite credible sources? were they properly attributed?).
- Fact-check failures and corrections handling.
- Bias and balance complaints.
- Editorial-policy adherence (per `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md` and successor editorial charters).
- Translation accuracy disputes (relevant to multilingual editions).
- Aggregation/clustering choices (did our cluster engine fairly represent the story?).
- NobuAI summary disputes (was the AI summary accurate / balanced?).
- Comment moderation (when comments ship per S1591 deferred queue).

Out-of-scope topics (route elsewhere):

- Business / commercial matters → Vader / Ray.
- Technical bugs → issue tracker.
- Personnel disputes → HR.
- Legal disputes → counsel (Ombudsman may flag legal concerns but is not counsel).

## 5. Intake channels

- **Web form** at `/ombudsman` (S2023 deferred).
- **Email alias** `ombudsman@grimbanews.com` (S2024 deferred — needs DNS + LeafRelay routing).
- **Anonymous tip channel** (S2025 deferred — needs SecureDrop or equivalent).
- **Postal mail** to publisher address per `/mentions-legales`.

Intake must support FR + EN today; expand per locale per S2032 + `docs/GRIMBANEWS_LEGAL_PAGES_LOCALIZATION_MATRIX.md`.

## 6. Investigation process

1. **Acknowledgement** — initial response within 14 days (S2028 SLA).
2. **Triage** — Ombudsman applies severity rubric (S2026):
   - **Inquiry** — request for explanation; resolve in 14-30 days.
   - **Complaint** — alleged breach; investigate in 30-60 days.
   - **Major investigation** — systemic concern; 60-180 days.
3. **Investigation** — interview involved editorial staff, review source materials, consult external standards (Trust Project per S2144, IFCN per S2148 if signatory).
4. **Findings** — written investigation log (S2027 — internal `ombudsman_investigations` table; per-investigation public summary).
5. **Publication** — public findings (S2029) within 7 days of investigation close; anonymized-but-public log of resolved cases (S2030).
6. **Correction issuance** — Ombudsman has correction-issuance authority per S2034 (gates on S2006 corrections primitive).

## 7. Reporting

- **Public:** per-investigation findings on `/ombudsman` (S2029).
- **Annual:** standalone Ombudsman annual report (S2031 — separate cadence from S2001 transparency report).
- **Quarterly:** office-hours session (S2037 — public Zoom or in-person).

## 8. Escalation to external bodies

For jurisdictional press councils per S2033:

- **France:** Conseil de déontologie journalistique et de médiation (CDJM).
- **Quebec:** Conseil de presse du Québec.
- **UK:** Independent Press Standards Organisation (IPSO) or IMPRESS.
- **US:** No national press council; route to relevant state journalism ethics body or self-publish.
- **EU broadly:** European Federation of Journalists.

Per-jurisdiction routing requires retained press counsel.

## 9. Charter approval + revision

- **Author:** v0 — operator-side draft (Wave RRRRRRRRRR, 2026-05-22).
- **Counsel review:** TBD.
- **Editorial board ratification:** TBD.
- **Operator ratification:** Vader.
- **Annual review:** mandatory.

## 10. Succession plan

- Search committee for next Ombudsman convened 12 months before current term ends.
- Committee: outgoing Ombudsman (advisory only), Lucy Leai, retained press counsel, one external journalist not affiliated with GrimbaNews.
- Selection criteria: prior senior journalism experience, demonstrated independence, multilingual capacity (FR + EN at minimum).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2021 row; gates for S2022-S2040)
- Sister policy library: `docs/GRIMBANEWS_POLICY_LIBRARY_INDEX.md`
- Editorial pivot context: `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md` (Ombudsman not currently on roster — S2022 hire)
- Standards references: AP/Reuters/BBC ombudsman charters as comparators; CDJM (France) statutes
