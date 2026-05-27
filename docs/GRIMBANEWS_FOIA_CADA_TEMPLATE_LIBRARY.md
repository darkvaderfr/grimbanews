# GrimbaNews — FOIA / CADA Template Library

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + counsel + investigative reporter (when hired)
**Walks:** Mythos S2204 (FOIA template library) deferred → partial
**Gating dependency:** Operator-side legal tooling.

## Why this exists

Per-jurisdiction access-to-information request templates speed investigative reporting. France: CADA (Commission d'accès aux documents administratifs). EU: Regulation 1049/2001. US: FOIA. Each has specific format + appeal procedure.

## Templates per jurisdiction

### France — CADA template

Letter to administration:
- Request specific docs (cite Article L300-1 du Code des relations entre le public et l'administration)
- 1-month statutory response window
- If silence/refusal: CADA appeal (free, 1-month resolution)
- If CADA agrees but admin still refuses: tribunal administratif

### EU — Reg. 1049/2001 template

- Letter to EU institution (Commission, Council, Parliament)
- 15 working days statutory response
- If refusal: confirmatory application
- If still refused: European Ombudsman OR Court of Justice

### US — FOIA template

- Letter to agency FOIA officer
- 20 business days statutory response
- Per-request fee waivers cited where applicable (educational, public-interest)
- Per-refusal MuckRock-style appeal templates

### Per-country others

- UK: FOIA 2000 template
- DE: Informationsfreiheitsgesetz template
- BR: Lei de Acesso à Informação template
- Per-country counsel review required

## Tracking

Per-request status in `/admin/grimba/foia-tracker`:
- Request date + jurisdiction
- Response window
- Outcome (granted / partial / refused / appealed)
- Per-request investigator notes

## Cross-references

Master plan: S2204. Sister: `docs/GRIMBANEWS_SECUREDROP_TIP_INTAKE_PLAN.md`, `docs/GRIMBANEWS_INVESTIGATIVE_REPORTER_HIRE_PROFILE.md`.
