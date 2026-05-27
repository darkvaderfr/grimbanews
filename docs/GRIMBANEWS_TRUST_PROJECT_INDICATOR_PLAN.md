# GrimbaNews — Trust Project Indicator Adoption Plan

**Status:** plan v0
**Owner:** Maya Patel (Compliance) + Henry Walker (Editorial) + Michael O'Connor (Tech Writer)
**Walks:** Mythos S2144 (Trust Project trust-indicator adoption) deferred → partial
**Gating dependency:** 8 trust indicators not implemented as machine-readable schema; per-article metadata extension needed.

## Why this exists

The Trust Project's 8 trust indicators are the closest thing to a cross-industry standard for news-trust signaling. Adoption signals to readers + aggregators that GrimbaNews meets a documented bar.

## The 8 indicators

1. Best Practices — sources, ownership, mission, ethics, corrections.
2. Author Expertise — biography, contact, areas of expertise.
3. Type of Work — news / opinion / analysis / sponsored / review.
4. Citations and References — sources cited.
5. Methods — how was this reported.
6. Locally Sourced — local correspondent / wire / aggregator.
7. Diverse Voices — editorial efforts on diversity.
8. Actionable Feedback — reader correction / feedback channels.

## v1 implementation

- Per-article schema.org markup using TrustProject-compatible vocabulary.
- Per-author profile page (S1411) carries indicators 1, 2, 7.
- Per-article body footer carries indicators 3, 4, 5, 6, 8.
- Methodology page maps each indicator to GrimbaNews-side surface.

## Application

- Apply for Trust Project member certification once all 8 are live.
- Annual recertification.
- Logo placement on `/methodologie` upon certification.

## Cross-references

Master plan: S2144. Sister: S2141 (program scope), S2145 (NewsGuard), S2146 (AllSides), S2148 (IFCN), S2001 (transparency).
