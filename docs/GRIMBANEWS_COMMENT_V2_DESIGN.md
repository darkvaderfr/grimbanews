# GrimbaNews — Community Comment v2 Design

**Status:** design v0 (no comments table; no comment surface)
**Owner:** Steve Jobs (CPO) on UX + Sara Chen (CISO) on safety + Lucy Leai (Strategy) on community guidelines
**Walks:** Mythos S1361 (comment system v1), S1368 (comment community guidelines), S1369 (comment moderator tooling) deferred → partial
**Gating dependency:** Moderation queue (per `docs/GRIMBANEWS_MODERATION_QUEUE_DESIGN.md`) + member-account-required policy + on-call moderator roster expansion. Schema + design itself is operator-side.

## Why this exists

S1361 + S1368 + S1369 share a root: GrimbaNews has **no comment surface** today. The deferral notes pointed at "no comments table." The first decision is whether to ship comments at all — peers split (Reuters dropped them; The Atlantic kept them; The Conversation built around them). This document proposes the v2 design (skipping a v1 single-thread, going straight to a moderation-centric v2) so when the call is made to ship, the design + community guidelines + tooling are ready.

## Decision point (Vader review required)

**Do we ship comments at all?** Options:

1. **Yes, full comments** — high engagement potential; high moderation cost; needs robust safety.
2. **Yes, member-only highlights** — readers highlight passages with private notes; opt-in publish to comment-stream. Lower moderation cost.
3. **No comments, only correction-request form + ombudsman intake** — lowest cost, no community surface.

This document assumes Option 1 ships eventually. Option 2 is the recommended phased approach.

## Phased rollout

### Phase 1 — Member highlights + private notes

- Logged-in members can highlight passages on article + attach private notes.
- Private to the member; not surfaced to others.
- No moderation needed (private).
- **Schema:** `member_article_highlights` (member_id, post_id, passage_offset, passage_length, note_text, created_at).
- Ships independently. Tests reader appetite for in-context engagement.

### Phase 2 — Opt-in public highlights

- Member can mark a highlight as **publish to comment stream**.
- Enters moderation queue per `docs/GRIMBANEWS_MODERATION_QUEUE_DESIGN.md`.
- On approve, surfaces in per-article comment list.
- Member can recall (unpublish) own comment any time.

### Phase 3 — Full per-article comment thread

- Member writes free-form comment (not anchored to passage).
- Same moderation flow.
- Thread depth = 2 (root + 1 reply level). Hard cap.
- Reactions: thoughtful, fact-check-this, disagree. **No "like" or vote score.** (Conscious anti-pattern avoidance per `docs/GRIMBANEWS_MODERATION_QUEUE_DESIGN.md`.)

### Phase 4 — Per-cluster discussion

- Comments span the cluster, not individual constituent articles.
- Surfaces the discussion across publisher boundaries.
- Higher-value, lower-noise (cluster level filters most pile-on).

## Schema (when shipped)

```sql
CREATE TABLE comments (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  member_id BIGINT NOT NULL,                  -- FK members.id (auth-required)
  post_id BIGINT NULL,                        -- FK posts.id (article-anchored)
  story_cluster_id BIGINT NULL,               -- FK story_clusters.id (cluster-anchored, Phase 4)
  parent_comment_id BIGINT NULL,              -- FK comments.id (1-level reply only)
  body TEXT NOT NULL,
  highlight_passage_offset INT NULL,          -- Phase 2 anchor
  highlight_passage_length INT NULL,          -- Phase 2 anchor
  status ENUM('pending','published','rejected','hidden_by_member','removed_by_mod') DEFAULT 'pending',
  reactions JSON DEFAULT '{}',                -- {thoughtful: N, fact_check: N, disagree: N}
  moderator_id BIGINT NULL,
  moderation_notes TEXT NULL,
  language CHAR(2) NULL,                      -- 'fr' / 'en' for translation routing
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX (post_id, status, created_at),
  INDEX (story_cluster_id, status, created_at),
  INDEX (member_id, status)
);
```

## Community guidelines (S1368 ship)

**Surface:** `/communaute/regles` (FR primary) + `/community/rules` (EN). Required link from every comment composer.

**Rules (v0 draft — Lucy Leai final):**

1. **Be precise.** Specific disagreement > vague disapproval.
2. **Source your claim.** Disputing a fact? Link the source.
3. **No personal attacks.** Argue the position, not the person.
4. **No doxxing, no harassment, no threats.** Zero tolerance; account-level ban.
5. **No hate speech.** Defined per Council of Europe / Article 19 standards.
6. **No spam, no link-stuffing, no commercial.**
7. **Respect the right-of-reply** of subjects mentioned in the article (refer to `docs/GRIMBANEWS_DMCA_RIGHT_OF_REPLY_POLICY.md` for formal channel).
8. **Multilingual welcome.** FR + EN primary; comments in other languages get auto-translation note.
9. **NobuAI may surface "Hmm, this could be checked" advisory** on disputed-fact claims. Advisory only; reader sees, mod sees, decision belongs to mod.
10. **Recall yours, request review of others.** Use the recall + flag buttons rather than re-escalate publicly.

**Enforcement ladder:**

| Violation | First | Second | Third |
|---|---|---|---|
| Spam / link-stuffing | Comment removed | 7-day cooldown | Account-level ban |
| Personal attack | Comment removed + warning DM | 30-day cooldown | Account-level ban |
| Hate speech | Comment removed + 30-day cooldown | Account-level ban | (escalate to law per `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`) |
| Doxxing / threat / NCII | Immediate ban + escalation | n/a | n/a |
| Repeat ban-evasion | Permanent IP-hash ban | n/a | n/a |

## Moderator tooling (S1369 ship)

**Surface:** `/admin/grimba/comments-queue` + `/admin/grimba/comments-history`.

**Per-comment actions:**
- Approve.
- Reject + reason.
- Hold + escalate.
- Edit (typo / safety-redaction) with edit-log visible to commenter.
- Issue cooldown DM (links to enforcement-ladder step).
- Ban (with reason + appeal info).

**Moderator dashboard:**
- Queue depth + per-severity counts.
- Per-moderator throughput.
- Per-moderator false-rejection rate (sampled by lead moderator).
- "Hot articles" — articles with > 50 pending comments (signal: brigading; flag per `docs/GRIMBANEWS_MODERATION_QUEUE_DESIGN.md` brigading detection).

**Cross-moderator coordination:**
- "Locked by [moderator]" badge on each comment to prevent double-handle.
- Per-day handoff note for cross-shift moderators.

## Public surface UX

- **Default state:** comments hidden behind "Show X comments" disclosure.
- **Per-comment chrome:** member name + avatar, timestamp, reactions, recall button (own only), flag button.
- **Highlight anchor (Phase 2):** highlights the passage on the article when comment is in view.
- **Sort:** "Thoughtful first" default (reactions['thoughtful'] DESC), with "Newest" toggle. **No "Most replies"** to avoid pile-on rewards.
- **Per-comment translation:** auto-translate on click via NobuTranslator chain (per `App\Services\GrimbaTranslator`). "Translated from [lang]" footer.
- **Per-comment "Why this is here" tooltip** explains "Approved by moderator on [date]" — every comment that's live has moderator stamp.

## Privacy posture

- **Comments tied to member account** — anonymous comments not permitted.
- **Account creation requires email-verify.**
- **Account ban writes IP-hash to ban list** (per `docs/GRIMBANEWS_MODERATION_QUEUE_DESIGN.md` schema).
- **GDPR erasure** removes member + cascades to soft-delete on own comments (per `docs/GRIMBANEWS_GDPR_ROPA.md`).
- **Right-of-reply** for comment-mentioned subjects routes to ombudsman per `docs/GRIMBANEWS_DMCA_RIGHT_OF_REPLY_POLICY.md` section 2.

## Engineering effort estimate

- Phase 1 (member highlights, private): 4 sprints.
- Phase 2 (opt-in public + moderation hook): 4 sprints.
- Phase 3 (full thread): 6 sprints.
- Phase 4 (cluster-level): 4 sprints.
- Community guidelines page + i18n: 1 sprint.
- Moderator tooling: 6 sprints.
- **Full ship Phase 1 → Phase 4: ~25 sprints, gates on Vader decision to ship comments at all.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1361-S1370)
- Sister docs: `docs/GRIMBANEWS_MODERATION_QUEUE_DESIGN.md`, `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`, `docs/GRIMBANEWS_DMCA_RIGHT_OF_REPLY_POLICY.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`
- Translator: `app/Services/GrimbaTranslator.php`
- Member auth: `Botble\Member` package
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
