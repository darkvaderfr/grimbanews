# GrimbaNews — Schools Program Accessibility Scope

**Status:** plan v0
**Owner:** Alex Morgan (UI/UX) + Maya Patel (Compliance) + Liam Smith (PM)
**Walks:** Mythos S2118 (schools program — accessibility for special-needs classrooms) deferred → partial
**Gating dependency:** S1571-S1580 A11y v3 (per honest deferral) + pedagogy-partner sign-off + per-jurisdiction special-needs assessment.

## Why this exists

Schools program (S2101+) without accessibility is not actually inclusive. Special-needs classrooms (dyslexia, low-vision, ADHD, autism-spectrum) require deliberate design adaptations beyond WCAG AA.

## v1 commitment scope

| Need | Adaptation |
|---|---|
| Dyslexia | Reader mode with OpenDyslexic font option + line-spacing slider |
| Low vision | High-contrast theme (already shipped per Wave WWW); zoom + voice-over compatibility |
| ADHD / focus | Distraction-free reader mode (hide rails, ads, comments) |
| Autism spectrum | Predictable navigation, no surprise modals, content-warning chips on intense topics |
| Hearing | Captions on any audio narration (gates on S1380 audio when shipped) |
| Motor | Full keyboard navigation, large touch targets (≥ 44px) |
| Cognitive load | Plain-language summary option (gates on NobuAI per S1099) |

## v1 implementation order

1. Reader mode + OpenDyslexic (low engineering cost).
2. Keyboard-nav audit.
3. Content-warning chip taxonomy.
4. Plain-language summary mode (gates on NobuAI primitives).
5. Audio captions (gates on audio narration ship).

## Cross-references

Master plan: S2118. Sister: S1571-S1580 (A11y v3 set), S2101-S2117 (schools pack), S2119 (multilingual deployment), S2125 (citizenship-prep).
