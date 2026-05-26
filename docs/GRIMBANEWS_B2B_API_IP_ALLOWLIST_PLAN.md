# GrimbaNews — B2B API IP Allowlist Plan

**Status:** plan v0 (no per-key IP allowlist; would be schema addition)
**Owner:** Sara Chen (CISO) signs requirement + Rajesh Kumar (Backend) implements middleware + Larry Ellison on JSON column shape + Liam Smith on partner UX
**Walks:** Mythos S1237 (B2B API v1 IP allowlist) deferred → partial
**Gating dependency:** `api_keys.ip_allowlist` JSON column + middleware enforcement

## Why this exists

S1237 lets enterprise partners restrict key use to known source IPs (data centers, VPNs). Defense-in-depth — leaked key is much less useful if attacker's IP isn't allowlisted.

## Today's surrogate

- **No allowlisting.**
- **Public web nginx access logs** capture source IP per request — observation only, not enforcement.

## Schema

```sql
ALTER TABLE api_keys ADD COLUMN ip_allowlist JSON DEFAULT NULL;
-- Example: ["203.0.113.0/24", "198.51.100.42/32"]
-- NULL = no allowlist enforcement (default for academic / starter tiers)
```

## Middleware (Rajesh Kumar)

```php
class ApiKeyIpAllowlistMiddleware
{
    public function handle($request, Closure $next) {
        $key = $request->user('api-v2'); // resolved by AuthMiddleware
        if (!$key || !$key->ip_allowlist) return $next($request);

        $clientIp = $request->ip();
        $allowed = collect($key->ip_allowlist)->some(fn($cidr) => IPv4::cidrMatch($clientIp, $cidr));

        if (!$allowed) {
            ApiKeyAuditLog::create([
                'api_key_id' => $key->id,
                'action' => 'ip_blocked',
                'reason' => "IP {$clientIp} not in allowlist",
                'ip' => $clientIp,
            ]);
            return response()->json(['error' => 'ip_not_allowlisted'], 403);
        }

        return $next($request);
    }
}
```

## Partner self-service UX (`/account/api-keys/{id}/allowlist`)

```
IP allowlist for "Partner X production"

[ Add CIDR ]  e.g., 203.0.113.0/24 or 198.51.100.42/32

Current entries:
  - 203.0.113.0/24    [Edit] [Remove]
  - 198.51.100.42/32  [Edit] [Remove]

⚠ Empty = no restriction. We strongly recommend at least one entry for production keys.
```

## CIDR validation

- IPv4 + IPv6 supported.
- Reject `/0` (matches everything) with error "Use of /0 defeats the purpose of allowlisting".
- Max 50 entries per key (operational cap).
- Soft warning at 10+ entries: "Consider consolidating".

## Audit + telemetry

- Every IP-blocked attempt logged.
- Per-key dashboard shows "Blocked attempts last 7d" — flags suspected leaks.
- Slack alert if blocked attempts exceed 10/hour for a single key.

## Allowlist sync to dev/staging

- Partner can copy allowlist between their keys via "Apply to all keys" CTA.
- Useful for partners with N keys (production / staging / dev) on same infra.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1237)
- Sister docs: `docs/GRIMBANEWS_B2B_API_V1_AUTH_PLAN.md`, `docs/GRIMBANEWS_B2B_API_KEY_ISSUANCE_PLAN.md`, `docs/GRIMBANEWS_API_V2_KEY_REVOCATION_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
