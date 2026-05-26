# GrimbaNews — Ombudsman Email Alias Provisioning

**Status:** plan v0 (no alias provisioned; needs DNS + Acelle inbox routing)
**Owner:** Jacob Lee (DevOps) on DNS + Hannah Kim on Acelle inbox config + Sara Chen on encryption
**Walks:** Mythos S2024 (Ombudsman intake — email alias) deferred → partial
**Gating dependency:** Domain DNS access + LeafRelay/Acelle inbox routing capacity.

## Why this exists

S2024 provisions `ombudsman@grimbanews.com` as a dedicated routing target. Bridges off-platform complainants (who'd rather email than fill a form) into the ombudsman workflow.

## Today's surrogate

- Generic `contact@grimbanews.com` (Acelle-served).

## DNS + delivery

- MX records already point to LeafRelay inbox cluster.
- Add `ombudsman` as a sub-alias delivered to ombudsman role mailbox.
- DKIM signing on outbound from this alias to preserve sender trust.

## Inbox config

- Dedicated inbox in Acelle (not just forward).
- Auto-ack template (S2023 intake page surrogate when complainant uses email path).
- 7-year retention with at-rest encryption.

## Triage workflow

- Inbound → matched against `takedown_requests` keywords → auto-flag if DMCA-shaped (route to S2003 ledger).
- Otherwise → ombudsman triage queue.
- Manual entry to `ombudsman_investigations` (S2027 schema doc).

## Anti-spam

- Standard spam filters at Acelle gateway.
- Allow-list for known legal-counsel domains (operator-curated).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2024)
- Sister docs: `docs/GRIMBANEWS_OMBUDSMAN_INTAKE_PAGE_SCOPE.md`, `docs/GRIMBANEWS_INVESTIGATION_LOG_SCHEMA.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
