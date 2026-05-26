# GrimbaNews — DMCA + Right-of-Reply Policy

**Status:** policy v0 (operator-side; awaiting counsel review)
**Owner:** Sara Chen (CISO) on intake + Lucy Leai (Strategy) on editorial side + retained press counsel for jurisdictional clauses
**Walks:** Mythos S1358 (DMCA / right-of-reply) deferred → partial
**Gating dependency:** Retained counsel for per-jurisdiction notice + counter-notice clauses (US DMCA, EU CDSM Art. 17, FR LCEN, UK CDPA). Policy draft itself is operator-side.

## Why this exists

S1358 was honest-deferred as "operator-side legal pickup." The policy needs to exist **before** the first takedown notice or right-of-reply request arrives, not after. This document is the v0 policy. It governs:

1. Copyright-infringement claims against content GrimbaNews republishes (DMCA-class).
2. Right-of-reply requests from subjects of articles (jurisdictional press laws).
3. Source-licensing disputes against partner content (per `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`).

## Today's surrogate

- `/.well-known/security.txt` — security-side intake (S1356 partial).
- `/contact` — generic intake (`App\Http\Controllers\GrimbaContactController`).
- `/mentions-legales` — operator address per FR LCEN obligation.
- **No dedicated DMCA / right-of-reply intake.**
- **No takedown ledger.**

## 1. DMCA-class copyright claims

### Intake channels

- **Email** — `dmca@grimbanews.com` (gates on DNS + LeafRelay routing; today routes to `/contact`).
- **Web form** at `/legal/dmca-notice` (deferred surface; today same as `/contact`).
- **Postal** — operator address per `/mentions-legales`.

### Required claim contents

Per US DMCA (17 USC § 512) and the EU's Digital Services Act notice-and-action rules:

1. Identification of copyrighted work.
2. Identification of URL where allegedly infringing material is hosted.
3. Claimant contact (name, address, email, phone).
4. Good-faith statement.
5. Accuracy + perjury statement.
6. Physical or electronic signature.

Incomplete notices receive a single response requesting completion; not actioned until complete.

### Response SLA

- **Acknowledgement:** within 5 business days.
- **Action (takedown or denial with reasoning):** within 14 business days.
- **Counter-notice window:** 14 days from takedown.
- **Re-publication on counter-notice:** within 10-14 days unless claimant files suit (per US DMCA standard; FR LCEN has narrower window).

### Action options

- **Takedown** — soft-delete `posts.id`; archive original metadata to `legal_takedowns` table (new — gates on schema below).
- **Counter-action by content owner** — re-publish if counter-notice valid.
- **Refuse + reasoning** — for non-infringing claims (fair-use, public-domain, license-covered).

### Schema (new, gates on first action)

```sql
CREATE TABLE legal_takedowns (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  type ENUM('dmca','right_of_reply','source_license_dispute','court_order','gdpr_erasure_extended') NOT NULL,
  affected_post_ids JSON NOT NULL,
  affected_cluster_ids JSON NULL,
  claimant_name VARCHAR(255) NOT NULL,
  claimant_jurisdiction VARCHAR(64) NULL,
  jurisdiction ENUM('us_dmca','fr_lcen','eu_dsa','uk_cdpa','other') NOT NULL,
  status ENUM('received','acknowledged','actioned','denied','counter_noticed','reinstated','closed') DEFAULT 'received',
  action_type ENUM('takedown','redaction','correction','annotation','no_action') NULL,
  received_at TIMESTAMP NOT NULL,
  acknowledged_at TIMESTAMP NULL,
  actioned_at TIMESTAMP NULL,
  reviewer_id BIGINT NOT NULL,
  legal_review_id VARCHAR(64) NULL,                 -- counsel docket reference
  rationale TEXT NULL,
  documents_path VARCHAR(255) NULL,                 -- storage/app/legal-takedowns/{id}/
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX (status, type),
  INDEX (received_at)
);
```

## 2. Right-of-reply

### Jurisdictional triggers

- **France** — droit de réponse (Loi du 29 juillet 1881, art. 13). 3-month limitation period.
- **Quebec** — Code civil + Loi sur la presse, varying.
- **EU broadly** — directive 89/552/EEC on broadcasting; press analog varies by member state.
- **US** — narrow common-law right-of-reply; no federal mandate.
- **UK** — IPSO / IMPRESS code obligations.

### Intake

- **Email** — `droit-de-reponse@grimbanews.com` + `right-of-reply@grimbanews.com` (gates on DNS).
- **Web form** at `/legal/right-of-reply` (deferred).
- **Postal** — operator address.

### Required contents

- Identification of article (URL + date).
- Specific passage(s) subject of reply.
- Proposed reply text.
- Identity of requester.
- Connection to subject matter (subject of article, person named, organization referenced).

### Response

- **Acknowledgement:** within 5 business days.
- **Decision:** within 8 days for FR-jurisdiction (legal deadline); within 14 business days otherwise.
- **Publication** of reply: same prominence as original article; ≤ original word count or one column, whichever greater.
- **Refusal grounds** — only for clearly defamatory, advertising-disguised, or already-corrected items. Refusal must be reasoned.

### Right-of-reply vs correction

- **Correction** — operator-initiated when we got facts wrong. Append-only per `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md` section 8.
- **Right-of-reply** — subject-initiated when subject contests characterization (regardless of factual accuracy).
- Both may apply to the same article (correction issued + reply published).

## 3. Source-licensing disputes (partner content)

Per `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md` syndication agreement clause 4.3 — Licensor may withdraw content on 24h notice. Routing:

- **Partner notifies** via partnership contact.
- **Operator depublishes** within 24h (per syndication agreement).
- **Logged to `legal_takedowns` with type='source_license_dispute'.**

## 4. Court orders

- **Acknowledgement:** within 24 hours of receipt.
- **Counsel review:** mandatory before action.
- **Action:** per court order, no broader.
- **Public disclosure** in annual transparency report per `docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md` (anonymized counts).

## 5. Operator workflow

```
[notice received] → [intake triage by Sara Chen] → [counsel review (urgent) | editorial review (non-urgent)]
                  → [decision] → [action] → [ledger entry] → [public counter if any] → [transparency report bucket]
```

## 6. Reader transparency

- **Annual transparency report** — `docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md` (deferred → will report aggregate counts of DMCA notices, right-of-reply requests, court orders by jurisdiction).
- **Per-takedown page disposition** — depublished pages show 410 Gone status with brief generic explanation; specific reason redacted unless court order requires disclosure.

## 7. Ombudsman scope

Per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` section 4, ombudsman has authority to review editorial decisions including correction handling. **Editorial side** of right-of-reply disputes is in ombudsman scope; **legal side** is counsel.

## Engineering effort estimate

- Schema + migration: 0.5 sprint.
- Intake routes + forms: 2 sprints.
- Operator review surface: 2 sprints.
- Counter-notice workflow: 1 sprint.
- Transparency-report aggregation hooks: 1 sprint.
- **Full ship: ~7 sprints once counsel-reviewed clauses land.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1358; sister S1356, S1357, S1359)
- Sister docs: `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`, `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md`, `docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md`, `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`
- Generic intake: `app/Http/Controllers/GrimbaContactController.php`
- Security intake reference: `/.well-known/security.txt`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
