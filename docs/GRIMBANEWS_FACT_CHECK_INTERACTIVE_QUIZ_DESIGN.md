# GrimbaNews — Fact-Check Primer Interactive Quiz

**Status:** plan v0
**Owner:** Liam Smith (PM) + Steve Jobs (CPO) + Nina Patel (Lead FE)
**Walks:** Mythos S1788 (fact-check primer interactive quiz) deferred → partial
**Gating dependency:** Quiz primitive (gates on S1721-S1730 literacy band).

## Quiz design

Per-fact-check-primer module, 5-10 question quiz at end:

- Multi-choice (4 options)
- Single-answer + explanation per option
- Per-question source citation
- Per-quiz timer (optional; gamification light)
- Per-quiz score visible to teacher (per classroom)

## Per-question types

- Identify primary source: shown 4 sources, pick the primary.
- Spot the misrepresentation: read excerpt, identify what's wrong.
- Cross-reference: given claim + 3 sources, pick which supports.
- Use the right tool: given claim, pick correct fact-check service.

## Schema (gates on Vader migration approval)

```
quizzes:
  id | module_slug | question_count | passing_score
quiz_questions:
  id | quiz_id | question_text | correct_answer_index | options JSON | explanation
quiz_attempts:
  id | quiz_id | student_id | score | completed_at
```

## Per-class teacher review

Per-class attempt log + per-question performance breakdown.

## Cross-references

Master plan: S1788. Sister: `docs/GRIMBANEWS_FACT_CHECK_PRIMER_SCHOOL_DISTRIBUTION.md`, `docs/GRIMBANEWS_CLASSROOM_ASSIGNMENT_PRIMITIVE.md`.
