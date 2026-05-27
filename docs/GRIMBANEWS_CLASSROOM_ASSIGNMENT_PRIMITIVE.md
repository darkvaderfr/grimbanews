# GrimbaNews — Classroom Assignment Primitive

**Status:** plan v0
**Owner:** Liam Smith (PM) + per-school editor
**Walks:** Mythos S1678 (classroom assignment primitive) deferred → partial
**Gating dependency:** Wave SUB-23 student progress dashboard.

## Schema (gates on Vader migration approval)

```
classroom_assignments:
  id | classroom_id | teacher_id | title | description | assigned_at | due_at
  | assignment_type (read|quiz|writing|discussion)
classroom_assignment_targets:
  assignment_id | post_id (nullable) | cluster_id (nullable)
classroom_assignment_submissions:
  assignment_id | student_id | submitted_at | response_text | grade (nullable)
```

## Teacher workflow

1. Teacher creates assignment via /classroom/{id}/assignments/new.
2. Picks GrimbaNews article or cluster as source.
3. Sets type (read for understanding / quiz / written response / discussion seed).
4. Sets due date.
5. Per-assignment auto-distributed to enrolled students.

## Student workflow

1. Per-student notifications surface new assignments.
2. Student completes per-assignment-type response.
3. Per-quiz auto-grading; per-written-response teacher review.

## Cross-references

Master plan: S1678. Sister: `docs/GRIMBANEWS_CLASSROOM_STUDENT_PROGRESS_DASHBOARD.md`, `docs/GRIMBANEWS_SCHOOLS_PROGRAM_EDU_SCOPE.md`.
