# GrimbaNews — NobuAI Reader Feedback (👍/👎) Surrogate Design

**Sprint ID:** S1089
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI feedback loop (👍/👎)`
**Walk wave:** BBBB (60-row Mythos deferred → partial)

## Gating dependency

A real 👍/👎 feedback loop on NobuAI summaries requires:

- A `nobuai_feedback` table (`post_id`, `member_id|cookie_hash`, `rating`, `created_at`)
- A POST endpoint (rate-limited + CSRF-guarded + consent-checked)
- A UI affordance under each `<x-grimba-nobuai-summary>` partial
- An aggregator that rolls per-summary scores into the trust-score band (S1088)
- A moderation queue for abuse (depends on S1591 moderation_queue, still deferred)

None of these are wired today. The summary partial currently renders as a passive read-only block under the article body. There is no thumbs UI, no endpoint, and no per-summary score column on the `posts` table.

## Surrogate-now infra

What we have today that approximates reader feedback on NobuAI output:

- **`/contact` form** — readers can describe a bad summary in free-form text; routes to ops mailbox
- **`grimba_nobuai_brand_lock` test** — `tests/Feature/GrimbaNobuAiBrandLockTest.php` blocks any user-visible provider-name leak (the highest-severity NobuAI quality bug we currently track)
- **Implicit signal — re-read rate** — `grimba_read` cookie + per-article presence tells us when readers revisit a story; an absent re-read on a story with a NobuAI summary is the weakest possible "this didn't help me" proxy
- **Methodology page disclosure** — `/methodologie` already tells readers the summary is machine-drafted, branded as NobuAI, and that feedback can be sent via `/contact`

## Why this is honest "partial" not "shipped"

A thumbs UI is a 2-day build (migration + endpoint + partial + JS handler + test). The reason it sits deferred is **not** technical — it is that thumbs-feedback without a moderation queue (S1591), an aggregator (S1088), and a per-summary version history (S1229) creates noise we cannot act on. Shipping the button without the closed loop would degrade the brand promise of `/methodologie`.

## Owners (per `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`)

- **Product:** Liam Smith (PM) + Steve Jobs (CPO) — feedback-loop UX scope
- **Backend:** Rajesh Kumar (Backend) — endpoint + rate limit + CSRF
- **Frontend:** Nina Patel (Lead FE) — thumbs affordance under `<x-grimba-nobuai-summary>`
- **Data:** David Chen (Data Scientist) — aggregation into S1088 trust score
- **Security:** Sara Chen (CISO) — abuse mitigation + consent check
- **QA:** Sara Kim — endpoint + UI regression tests
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1089 row)
- Mythos evidence anchor: `docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090`
- Trust-score gating: `docs/GRIMBANEWS_NOBUAI_READER_TRUST_SCORE_DESIGN.md` (S1088 sibling walk)
- Moderation-queue gate: `docs/GRIMBANEWS_TRUST_SAFETY_MODERATION_QUEUE_SCOPE.md` (KKKK band)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
