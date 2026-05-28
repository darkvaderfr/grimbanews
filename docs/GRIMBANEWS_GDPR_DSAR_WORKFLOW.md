# GrimbaNews — GDPR DSAR Workflow

**Status:** plan v0
**Owner:** Sara Chen (CISO) + counsel + DPO (if appointed) + Lucy Leai
**Walks:** Mythos S1858 (GDPR DSAR workflow) deferred → partial
**Gating dependency:** No formal DSAR intake today; /contact page is the surrogate intake.

## v1 formal DSAR intake

`/vos-droits` (FR) / `/your-rights` (EN) page (per Wave KKKK reader-rights page):

- Per-reader request form:
  - Identity verification (account login OR per-email-link tokenized).
  - Request type: access (Art. 15) / rectification (Art. 16) / erasure (Art. 17) / portability (Art. 20) / objection (Art. 21).
  - Per-request scope: all data / specific dataset.
  - Per-request response format: JSON / CSV / PDF.
- Per-request token + tracking ID.

## SLA per GDPR

- 30 days response (extendable to 60 if justified).
- Per-request auto-acknowledgment within 24h.
- Per-request operator-side resolution within 30 days.

## Per-request operator workflow

1. Request received via /vos-droits.
2. Auto-acknowledged email sent.
3. Identity verified (per-email-link OR account auth).
4. Per-request type: routed to appropriate operator-side process.
5. Per-data bundle prepared (per Wave LLL PII inventory + DSAR pattern).
6. Per-response delivered via secure encrypted bundle (passphrase per reader).
7. Per-request logged in DSAR register.

## Per-DSAR-register

```
dsar_requests:
  id | reader_email | request_type | scope | submitted_at | acknowledged_at
   | verified_at | resolved_at | response_url (expired in 30d)
```

## Per-quarter Sara review

- Per-quarter SLA compliance review.
- Per-incident pattern surfacing.

## Cross-references

Master plan: S1858. Sister: `docs/GRIMBANEWS_VOS_DROITS_READER_RIGHTS_PAGE_SCOPE.md` (Wave KKKK), `docs/GRIMBANEWS_PII_FIELD_INVENTORY.md`.
