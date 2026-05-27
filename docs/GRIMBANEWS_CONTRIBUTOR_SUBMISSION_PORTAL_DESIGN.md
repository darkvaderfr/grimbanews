# GrimbaNews — Contributor Submission Portal Design

**Status:** plan v0
**Owner:** Henry Walker (Editorial) + Liam Smith (PM) + Alex Morgan (UI/UX)
**Walks:** Mythos S1454 (contributor submission portal) deferred → partial
**Gating dependency:** contributor intake form (S1451) + S1411 multi-editor workflow + auth-level for contributors.

## Why this exists

Once the contributor program exists (S1451-S1459), contributors need a dedicated submission surface. Surrogate today is the operator-managed `/admin/grimba/rss-drafts` queue, which is not designed for external authors.

## v1 surface

- New route: `/contribuer` (public landing) → `/contribuer/dashboard` (auth-walled).
- Submission form: title, slug, body (markdown + WYSIWYG), category, attached sources (URLs), proposed publish date, declared conflicts of interest.
- Draft auto-save (5-minute interval).
- Submission triggers editor-side review queue entry.

## Status machine

```
draft → submitted → in_review → revision_requested ⇄ resubmitted → accepted → scheduled → published
                                                               → rejected
```

## Editor side

- Reviewers see queue at `/admin/grimba/contributor-queue`.
- Per-submission diff view if revision requested.
- Final accept publishes via existing post pipeline + attribution to contributor profile.

## Schema (additive to S1411 author system)

```sql
CREATE TABLE contributor_submissions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  contributor_user_id BIGINT NOT NULL,
  title VARCHAR(255),
  slug VARCHAR(140),
  body_md LONGTEXT,
  category_id BIGINT NULL,
  status ENUM('draft', 'submitted', 'in_review', 'revision_requested', 'accepted', 'rejected', 'scheduled', 'published') DEFAULT 'draft',
  reviewer_user_id BIGINT NULL,
  conflict_disclosure TEXT NULL,
  proposed_publish_at TIMESTAMP NULL,
  published_post_id BIGINT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status, updated_at)
);
```

## Cross-references

Master plan: S1454. Sister: S1451 (intake), S1452 (profile + verification), S1455 (editor handoff), S1456 (payout), S1313 (author trust badge progression).
