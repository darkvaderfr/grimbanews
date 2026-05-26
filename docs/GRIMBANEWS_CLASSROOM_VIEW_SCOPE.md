# GrimbaNews — Classroom View Scope

**Status:** scope v0 (no /classroom route; vault + coffre/share are the surrogates)
**Owner:** Lucy Leai (Strategy) + Steve Jobs (CPO) on UX + Ray Dalio (CFO) on educator discount
**Walks:** Mythos S1671 (classroom — /classroom route), S1672 (no-ads mode), S1673 (simplified UI), S1674 (teacher schema) deferred → partial
**Gating dependency:** Member role extension (`role='educator'`) + classroom seat schema + paid tier (per `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`). Scope itself is operator-side.

## Why this exists

S1671 was honest-deferred: "no classroom route." Same root for S1672-S1680 sister rows. Classroom mode is a coherent product story (educator-curated reading lists + student-progress + no-ads + simplified UI), not a single route. This document defines the scope so engineering can sequence the build.

## Today's surrogates

- `App\Support\GrimbaVault` (cookie + member sync via `members.vault_digest_post_ids`) — single-user reading list (per S1675 partial).
- `/coffre/share` route at `platform/themes/echo/views/coffre-share.blade.php` — one-off vault-share link (per S1676 partial).
- `App\Support\GrimbaAds::shouldRender()` — single gate; can be extended for `?no-ads=1` or member-role check (per S1672 partial).
- No `educator_seats` / `classrooms` / `students` tables (per S1674 deferred).
- No teacher-account schema or role.

## Product story

**Persona:** Middle/high school teacher or college instructor (FR + EN regions).

**Job to be done:**
1. Find current-events articles relevant to today's lesson.
2. Curate a small reading set for students.
3. Share with class via a shareable link (no student account required initially).
4. Optionally: track student progress (gates on student account creation per S1677).
5. Optionally: discuss in class via shared discussion thread (gates on `docs/GRIMBANEWS_COMMENT_V2_DESIGN.md` Phase 2+).

**Cost-friction reduction:**
- Free tier: 1 classroom, up to 30 students-as-anonymous-links, no ad surfaces.
- Educator-paid tier (per `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`): multiple classrooms, named-student accounts, progress dashboard.

## Schema (S1674 ship target)

```sql
CREATE TABLE classrooms (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  teacher_member_id BIGINT NOT NULL,        -- FK members.id (role='educator')
  name VARCHAR(128) NOT NULL,
  slug VARCHAR(64) NOT NULL,
  description TEXT NULL,
  language CHAR(2) DEFAULT 'fr',
  is_active BOOLEAN DEFAULT TRUE,
  share_token CHAR(40) NOT NULL UNIQUE,     -- for anonymous student link access
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX (teacher_member_id, is_active)
);

CREATE TABLE classroom_readings (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  classroom_id BIGINT NOT NULL,
  post_id BIGINT NOT NULL,                  -- FK posts.id
  added_by_member_id BIGINT NOT NULL,
  position TINYINT DEFAULT 0,
  teacher_note TEXT NULL,
  required BOOLEAN DEFAULT FALSE,
  due_at TIMESTAMP NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE (classroom_id, post_id),
  INDEX (classroom_id, position)
);

CREATE TABLE classroom_students (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  classroom_id BIGINT NOT NULL,
  student_member_id BIGINT NULL,            -- NULL for anonymous-link tier
  display_name VARCHAR(64) NULL,            -- pseudonymous label for anonymous tier
  joined_at TIMESTAMP NOT NULL,
  left_at TIMESTAMP NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX (classroom_id),
  INDEX (student_member_id)
);

CREATE TABLE student_reads (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  classroom_student_id BIGINT NOT NULL,
  classroom_reading_id BIGINT NOT NULL,
  opened_at TIMESTAMP NULL,
  completed_at TIMESTAMP NULL,              -- self-reported "I read this"
  dwell_seconds INT NULL,                   -- optional, opt-in only
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE (classroom_student_id, classroom_reading_id)
);
```

## Routes

- `/classroom` — educator-only landing; list of classrooms.
- `/classroom/{slug}/manage` — educator-only editor.
- `/classroom/{slug}` — student-facing reading list (gates on `share_token` in query for anonymous tier, or session for logged-in students).
- `/classroom/{slug}/progress` — educator-only progress dashboard (gates on student account tier).

## Layout (S1673 dependency)

Simplified UI variant (`platform/themes/echo/layouts/classroom-chrome.blade.php` — new):

- No global nav (replaced by classroom-name + teacher-name banner).
- No share kit (per per-classroom share link only).
- No ad surfaces (S1672 ship).
- No newsletter signup.
- Single-column reading list.
- Per-reading: title + 1-line dek + estimated reading time + "Mark read" button.
- Teacher note rendered inline above each reading.
- "Due in X days" highlight for required readings with due_at.

## No-ads mode (S1672 ship)

Extend `App\Support\GrimbaAds::shouldRender()`:

```php
public static function shouldRender(?Member $member = null, Request $request): bool
{
    // Existing checks: cookie consent, admin-disable, member opt-out, ...

    // New: query param + classroom mode
    if ($request->query('no_ads') === '1' || $request->is('classroom/*')) {
        return false;
    }
    if ($member?->role === 'educator' || $member?->role === 'student') {
        return false;
    }

    return true;
}
```

## Teacher discount tier (S1679)

Gates on `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md` paid tier:

| Tier | Price | Includes |
|---|---|---|
| Free | €0 | 1 classroom, 30 anonymous-link students, no progress dashboard |
| Educator | €5/mo or €40/year | Unlimited classrooms, 100 named students, progress dashboard |
| Institution | €40/mo per school | Unlimited everything, admin-dashboard, SSO, bulk teacher accounts |

Stripe-managed per paid-tier scope.

## Privacy posture (critical for minor-age students)

- **Default-anonymous tier** — students do not need accounts. Teacher distributes share-link; students access via link only.
- **Named-student tier** — requires explicit teacher confirmation of parental-consent compliance per jurisdiction (US COPPA, EU GDPR-K, FR CNIL minor rules).
- **Per-student data minimization** — only `opened_at`, `completed_at`, optional `dwell_seconds`. No PII beyond display_name.
- **Per-classroom data retention** — 1 year after `classroom.left_at`; auto-purge.
- **GDPR data export + erasure** honored per `docs/GRIMBANEWS_GDPR_ROPA.md`.
- **No targeted ads, no behavioral profiling** — locked by classroom-mode check.

## Bias-spread + diversity floor

Classroom mode keeps bias-spread floor from `docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md` reader-trust guards. If teacher curates a reading list that's bias-skewed, surface a soft advisory: "This reading list is heavily [left/center/right]. Consider adding a [other-side] piece." — non-blocking, just informational.

## Engineering effort estimate

- Schema + migrations: 1 sprint.
- Educator role extension on members: 0.5 sprint.
- Classroom CRUD + reading curation UI: 4 sprints.
- Simplified classroom layout: 2 sprints.
- No-ads mode extension: 0.5 sprint.
- Student-side reading view + mark-read tracking: 3 sprints.
- Progress dashboard (paid tier): 3 sprints.
- Per-tier paywall integration: 2 sprints (gates on Stripe).
- Privacy / minor-consent flow: 2 sprints.
- Tests + a11y: 2 sprints.
- **Full ship: ~20 sprints, gates on paid tier + educator role decision.**

## Launch playbook (S1680 gate)

1. Phase 0: paid tier shipped (per `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`).
2. Phase 1: Free tier with anonymous-link students. Soft-launch via 5 pilot teachers (Lucy's network).
3. Phase 2: Educator paid tier + progress dashboard.
4. Phase 3: Institution tier + SSO.
5. Phase 4: Partner-school integration (per `docs/GRIMBANEWS_BIAS_BAR_TUTORIAL_OVERLAY_DESIGN.md` partner-school dependency S1778).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1671-S1680)
- Sister docs: `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`, `docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_COMMENT_V2_DESIGN.md`
- Existing vault surface: `app/Support/GrimbaVault.php`, `platform/themes/echo/views/coffre-share.blade.php`
- Ads gate: `app/Support/GrimbaAds.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
