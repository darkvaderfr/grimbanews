# GrimbaNews — Privacy Program v2 Launch Readiness

**Status:** checklist v0
**Owner:** Sara Chen (CISO) + counsel + Vader
**Walks:** Mythos S1870 (privacy program v2 launch readiness) deferred → partial
**Gating dependency:** S1861-S1869 (all privacy v2 infrastructure shipped).

## Launch readiness checklist

- [ ] Cookie purpose classification documented (Wave SUB-48).
- [ ] Cookie lifetime audit complete (Wave SUB-49).
- [ ] Per-category granular consent toggles live (Wave SUB-49).
- [ ] Per-locale cookie banner variants live (Wave WWW).
- [ ] Privacy-program metrics dashboard live (Wave SUB-49).
- [ ] Consent log retention configured (Wave LLL).
- [ ] Per-staff updated privacy training delivered.
- [ ] Per-incident privacy-breach workflow tested.
- [ ] Per-quarter Sara + counsel review scheduled.

## v1 → v2 migration

- Existing v1 banner state (binary): treated as "strict + functional opt-in" → migrate forward gracefully.
- Per-reader: v2 banner shown on next visit + per-category preferences carried forward.

## Per-launch decision

Sara + counsel + Vader review. All items checked = ready for privacy program v2 go-live.

## Cross-references

Master plan: S1870. Sister: `docs/GRIMBANEWS_GDPR_LAUNCH_READINESS.md`, `docs/GRIMBANEWS_PRIVACY_METRICS_DASHBOARD.md`.
