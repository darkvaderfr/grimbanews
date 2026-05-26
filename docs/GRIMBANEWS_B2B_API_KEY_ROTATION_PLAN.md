# GrimbaNews — B2B API Key Rotation Plan

**Status:** plan v0 (no key store to rotate)
**Owner:** Sara Chen (CISO) signs cadence + Rajesh Kumar implements + Michael O'Connor writes partner docs
**Walks:** Mythos S1235 (B2B API v1 key rotation) deferred → partial
**Gating dependency:** `api_keys` table shipped + partner self-service portal

## Why this exists

S1235 lets partners rotate keys without service interruption. Critical for partner security hygiene + post-incident recovery. Without it, rotation = revoke + reissue = downtime.

## Today's surrogate

- **`.env` rotation** for internal shared secrets follows revoke + reissue + redeploy pattern — accepts short downtime, not partner-acceptable.

## Rotation modes

### Self-service (partner-initiated)

1. Partner clicks "Rotate key" in `/account/api-keys`.
2. System generates new key alongside old.
3. **24-hour overlap window** — both keys valid.
4. Partner deploys new key in their infra.
5. Old key auto-revoked at hour 24 (cron `grimba:api-keys-revoke-expired`).
6. Partner notified at hour 1, 12, and 23 by email of pending revocation.

### Operator-initiated (security event)

1. Admin clicks "Force rotate" on key.
2. New key generated.
3. **0-hour overlap** — old key revoked immediately.
4. Partner notified via email + Slack DM.
5. Partner contact phone (if on file) auto-called for P0 events.

### Scheduled (annual)

- Per `api_keys.expires_at`, 60d / 30d / 7d email reminders sent.
- Partner can rotate self-service.
- On expiry: 24h grace, then auto-revoke.

## Rotation logging

`api_key_audit_log` captures:
- `action='rotated'`
- old key_hash + new key_hash (both)
- actor (partner self-service vs admin)
- timestamp
- IP

## Code path

```php
// Self-service rotate
public function rotate($oldKeyId) {
    $old = ApiKey::findOrFail($oldKeyId);
    Gate::authorize('rotate', $old);

    $newSecret = bin2hex(random_bytes(16));
    $new = $old->replicate();
    $new->key_hash = hash('sha256', $newSecret);
    $new->key_prefix = $this->derivePrefix($newSecret, $old->tier);
    $new->expires_at = now()->addDays(365);
    $new->save();

    $old->update(['expires_at' => now()->addHours(24)]);

    ApiKeyAuditLog::create([
        'api_key_id' => $new->id,
        'action' => 'rotated',
        'reason' => 'self-service-rotate',
        'actor' => Auth::user()->email,
        'metadata' => ['old_id' => $old->id],
    ]);

    return ['key' => "gn_b2b_{$old->tier}_{$newSecret}"];  // one-time display
}
```

## Partner-side guidance (Michael O'Connor docs)

"Rotation best practice":
1. Generate new key.
2. Deploy to all production systems before old key revoked.
3. Confirm at least one production request succeeded with new key.
4. Mark rotation complete.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1235)
- Sister docs: `docs/GRIMBANEWS_B2B_API_KEY_ISSUANCE_PLAN.md`, `docs/GRIMBANEWS_API_V2_KEY_REVOCATION_PLAN.md`, `docs/GRIMBANEWS_B2B_API_V1_AUTH_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
