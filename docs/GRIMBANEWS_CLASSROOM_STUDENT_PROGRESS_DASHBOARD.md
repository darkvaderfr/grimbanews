# GrimbaNews — Classroom Student Progress Dashboard

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + school-partnership editor + Liam Smith (PM)
**Walks:** Mythos S1677 (Classroom student progress dashboard) deferred → partial
**Gating dependency:** `student_reads` table; per-classroom auth (gates on schools program S1712 partial).

## Why this exists

Teacher running media-literacy class needs per-student progress visibility: which articles read, which curriculum modules completed, per-quiz scores.

## Schema (gates on Vader migration approval)

```
classrooms:
  id | school_id | teacher_member_id | name | created_at
classroom_students:
  classroom_id | student_member_id | enrolled_at
student_reads:
  student_member_id | post_id | read_at | scroll_depth_pct | quiz_completed
classroom_assignments:
  id | classroom_id | post_id | due_at | curriculum_module
```

## Teacher dashboard

`/admin/classroom/{id}`:
- Per-student progress: assignments completed / total.
- Per-student curriculum-module score.
- Per-classroom engagement avg.
- Per-classroom per-student outliers (need help / excelling).

## Per-student privacy

- Student data: only teacher + student see.
- Per-school admin opt-in.
- Per-jurisdiction COPPA / GDPR-K compliance.
- Per-student opt-out + DSAR.

## Cross-references

Master plan: S1677. Sister: `docs/GRIMBANEWS_SCHOOLS_PROGRAM_EDU_SCOPE.md`, `docs/GRIMBANEWS_CLASSROOM_VIEW_SCOPE.md` (Wave LLL).
