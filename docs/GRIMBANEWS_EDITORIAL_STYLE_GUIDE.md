# GrimbaNews — Editorial Style Guide (v0)

**Status:** guide v0 (no enforcement layer; operator-side reference)
**Owner:** Lucy Leai (Strategy) + Steve Jobs (CPO) on voice
**Walks:** Mythos S1319 (editorial style guide enforcement) deferred → partial
**Gating dependency:** In-house composing (S1311 per `docs/GRIMBANEWS_INHOUSE_SOURCE_EDITOR_SCOPE.md`). The guide itself is operator-side; enforcement (pre-publish lint, NobuAI advisory) lands once compose surface ships.

## Why this exists

S1319 was honest-deferred: "operator-side editorial guide." Operator-side ≠ unwritten — this **is** the guide. It governs in-house composing (when S1311 ships) and provides reference voice for NobuAI-assisted summaries on aggregated posts. Enforcement layer (lint + advisory) lands as a separate sprint per scope below.

## 1. Voice + tone

- **Calm + precise.** GrimbaNews does not chase outrage. Where competing outlets use exclamation marks, we use periods.
- **Reader-respectful.** Assume the reader is intelligent, busy, and has read other coverage. Don't condescend; don't re-explain context already covered in our sister pieces (cross-link instead).
- **Multilingual-first.** Every piece may be read by francophone Africa, francophone Europe, anglophone Africa, anglophone diaspora. Avoid metropolitan-French-only idioms; avoid US-anglophone-only references.
- **Not opinion-prone in headlines.** Headlines describe; analysis-tagged pieces argue.

## 2. Sourcing rules

- **Minimum 2 sources** for news pieces, 3 for investigations. Editorials exempt.
- **Cite where claim originates** — link the upstream source. Wikipedia is not a primary source.
- **Quote attribution required** — every quote names the speaker + the venue + the date.
- **Anonymous sources** — require lead-editor approval; documented in `editorial_assignments.notes`.
- **NobuAI-generated summaries on aggregated clusters** must label sources from `posts.source_citations`.

## 3. Bias self-disclosure

- Author may **self-declare** bias position (left / center / right / unclear) at compose time per `docs/GRIMBANEWS_INHOUSE_SOURCE_EDITOR_SCOPE.md`.
- `App\Services\GrimbaBiasClassifier` second-pass confirms or flags discrepancy.
- Discrepancy → lead-editor review before publish.

## 4. Headline rules

- ≤ 75 characters for primary headline (mobile-readable).
- Dek (subheadline) ≤ 150 characters.
- No clickbait. No "you won't believe…", no "[N] things you need to know" framing.
- French + English versions when piece is multilingual.

## 5. Image policy

- Hero image required.
- Caption required (factual; if generative, label "Illustrative — generative" per S2123 deferred).
- Right-of-use verified: own / licensed / public-domain. Per-source license honored.
- Image-proxy enforced (per `docs/PUBLISHER_IMAGE_PROXY_DIAGNOSIS.md`) — no hotlinking.

## 6. Multilingual rules

- **Primary language** declared per piece (`posts.original_language`).
- **Auto-translation** allowed via NobuTranslator chain (per `App\Services\GrimbaTranslator`) but **must carry "Auto-translated — see original" footer** until human-reviewed.
- Human translator-reviewed pieces flagged in `posts.translation_status='human_reviewed'`.
- FR-CA / FR-FR / FR-AFR variants tolerated; don't force one standard (S1624 deferred).

## 7. NobuAI usage

- NobuAI assists with: outline-to-draft, summary, grammar check, fact-claim flagging.
- NobuAI **does not** publish without operator review.
- Reader-facing copy says "NobuAI" never the underlying provider (per `feedback_nobuai_model_branding.md`).
- "Drafted by NobuAI, edited by [author]" attribution when 50%+ generated.

## 8. Corrections + transparency

- Corrections **append-only** (never silent edits).
- Format:
  > **Correction (date):** [What was wrong] [What's now accurate].
- Logged in `posts.corrections` JSON column (gates on S2006 corrections primitive).
- Ombudsman reviewable per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` section 4.

## 9. Anti-pattern list

- ❌ "Some people say…" without naming who.
- ❌ Quoting an opinion piece as if it were a news report.
- ❌ Single-source breaking news without explicit "single-source — caution" flag.
- ❌ Bias-bar piece without running `App\Services\GrimbaBiasClassifier` for second-pass confirmation.
- ❌ Headline that contradicts the lede.
- ❌ AI summary that contradicts source citations.
- ❌ External provider names ("Powered by Anthropic / Claude / OpenAI") in reader-facing copy.

## 10. Enforcement (when compose surface ships per S1311)

**Pre-publish lint (blocking):**
- ≥ 2 source citations on news pieces.
- Dek present.
- Hero image present + caption.
- Headline ≤ 75 chars.
- No "NobuAI generated" without author attribution on > 50% NobuAI pieces.
- No external LLM provider names in copy (regex check vs `feedback_nobuai_model_branding.md` blocklist).

**Pre-publish NobuAI advisory (non-blocking):**
- Grammar check.
- Headline ↔ lede consistency.
- Fact-claim flagging (any unsourced claim of fact).
- Bias-self-disclosure ↔ classifier consistency.

Implementation lands at `app/Services/GrimbaEditorialLint.php` (new) called from `editorial_compose` save path.

## 11. Review cadence

- **Quarterly review** by Lucy + lead editor.
- **Annual revision** by full editorial board.
- **Ombudsman-triggered review** when investigation finds style-guide gap (per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` section 4).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1319; relates to S1311, S1317, S1320, S2006)
- Sister docs: `docs/GRIMBANEWS_INHOUSE_SOURCE_EDITOR_SCOPE.md`, `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`, `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`
- Source-classification policy: `docs/GRIMBANEWS_SOURCE_CLASSIFICATION_METHODOLOGY.md`
- Classifier surfaces: `app/Services/GrimbaBiasClassifier.php`, `app/Services/GrimbaCategoryClassifier.php`
- Translator: `app/Services/GrimbaTranslator.php`
- Brand-purity rules (NobuAI label): `~/.claude/projects/-Users-vb-kaizen/memory/feedback_nobuai_model_branding.md`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
