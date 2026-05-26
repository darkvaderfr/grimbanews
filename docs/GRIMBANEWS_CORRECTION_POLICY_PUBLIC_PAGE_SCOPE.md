# GrimbaNews — Correction Policy Public Page Scope

**Status:** plan v0 (no /corrections route; /mentions-legales legal page is parallel surrogate)
**Owner:** Lucy Leai (Strategy) signs policy + Liam Smith (PM) on copy + Steve Jobs (CPO) on design + Henry Walker (Content) drafts
**Walks:** Mythos S1438 (Correction-policy public page) deferred → partial
**Gating dependency:** Correction primitive shipped (S1433) + editorial board sign-off on policy

## Why this exists

S1438 is the reader-facing accountability document. Says explicitly: "Here's what we do when we get something wrong." Without it, readers have no contract — corrections feel arbitrary.

## Today's surrogate

- **`/mentions-legales`** — generic legal page; doesn't cover corrections specifically.
- **`/methodology`** — explains bias rubric + ingestion; doesn't cover corrections.

## Route

- FR: `/corrections` (also accessible as `/politique-corrections`)
- EN: `/corrections-policy`

## Sections

### 1 — Promise

```
At GrimbaNews, we get things wrong. When we do,
we tell you, we say what was wrong, and we leave
the record visible. We do not silently rewrite
history. We do not pretend the error never
happened.
```

### 2 — How to flag a correction request

- Form at `/contact?topic=correction` (existing contact form, dropdown reason).
- Email at `corrections@grimbanews.com` (gates on alias provisioning).
- Per-article "Suggest a correction" link (gates on per-article action UI).

### 3 — Our timeline

| Severity | Initial response | Full correction issued |
|---|---|---|
| Typo / minor | 7 days | when convenient |
| Factual error | 48 hours | within 7 days of confirmation |
| Retraction (article cannot stand) | 24 hours | immediate |

### 4 — How corrections look

- Visible badge on article (per S1433 design).
- Correction notice at top of article text.
- Cluster-level badge if part of MG dossier (per S1435).
- Email to readers who saved the article.

### 5 — What we DON'T do

- Silent edit + repost.
- Remove the original error text from the public record (we cross it out, we don't erase).
- Issue corrections only via social media.

### 6 — Annual transparency

- Transparency report (per `docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md`) includes per-year correction counts.
- Per-source correction rate (when meaningful).
- Per-journalist correction rate (per S1418 author analytics).

### 7 — Disputes

- If a correction request is denied, reader can escalate to Ombudsman (per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`).

## SEO + linking

- Linked from footer of every page.
- JSON-LD as `WebPage` with `about` = "editorial corrections policy".
- Hreflang FR + EN alternates.

## Locale rollout

- FR + EN at launch.
- ES, PT-BR, DE follow per locale launch ops.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1438)
- Sister docs: `docs/GRIMBANEWS_CORRECTION_NOTICE_BADGE_DESIGN.md`, `docs/GRIMBANEWS_CLUSTER_LEVEL_CORRECTION_PROPAGATION.md`, `docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md`, `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`, `docs/GRIMBANEWS_DMCA_RIGHT_OF_REPLY_POLICY.md`
- Existing: `/mentions-legales`, `/methodology`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
