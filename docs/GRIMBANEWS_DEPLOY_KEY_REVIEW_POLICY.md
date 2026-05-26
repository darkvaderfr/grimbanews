# GrimbaNews — Deploy Key Review Policy

**Status:** policy v0 (operator-side)
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps)
**Walks:** Mythos S946 (deploy key review) deferred → partial
**Gating dependency:** Operator-side SSH key inventory.

## Scope

Every SSH deploy key with access to:
- `darkvaderfr` GitHub org (read or write)
- GrimbaNews VPS (production, staging)
- Any third-party deploy target (CDN, S3 bucket, etc.)

## Quarterly review

Every quarter, operator runs:

```
cat /Users/vb/.ssh/authorized_keys
ssh vps "cat ~/.ssh/authorized_keys"
gh api /orgs/darkvaderfr/actions/secrets
```

And confirms:
1. Every key in `authorized_keys` maps to a current team member or active service.
2. No keys older than 12 months unless explicitly re-approved.
3. No keys for departed staff.
4. GitHub Actions secrets list matches expected automation surface.

## Per-key metadata (operator-managed local file)

`docs/internal/deploy-keys-inventory.md` (not in repo):

```
| Fingerprint | Owner | Service | Expiry | Last-reviewed |
|---|---|---|---|---|
| SHA256:... | Vader (laptop) | dev | 2026-12 | 2026-05 |
| SHA256:... | Vader (yubikey) | prod | n/a | 2026-05 |
| SHA256:... | github-actions-grimbanews | CI deploy | annual | 2026-05 |
```

## Rotation triggers

- Staff departure
- Laptop loss
- Suspected key compromise
- 12-month max age

## Surrogate today

Single-operator team (Vader). Key reviews happen ad-hoc. Formal cadence kicks in when team grows past 2 members touching prod.

## Cross-references

Master plan: S946. Sister: `docs/GRIMBANEWS_SECRET_ROTATION_RUNBOOK.md`.
