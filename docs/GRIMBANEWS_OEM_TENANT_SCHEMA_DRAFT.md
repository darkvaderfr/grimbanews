# GrimbaNews — OEM / Multi-Tenant Schema Draft

**Status:** schema design draft (no implementation; design ready for ship sprint)
**Owner:** Larry Ellison (VP DBA) + Jacob Lee (DevOps)
**Walks:** Mythos S1191 (OEM whitelabel config schema) deferred → partial
**Gating dependency:** None to design; implementation gates on a real B2B OEM customer commitment (per S1199 case-study row). Design itself is operator-side.

## Why this exists

S1191 was honest-deferred as "no tenants/tenant_settings table; current settings table is global." The reason is no B2B customer demands it today — but the **schema design** is operator-side work that doesn't depend on the first customer. Shipping the design walks S1191 from deferred (no plan) to partial (design exists, ready for first-customer-triggered ship sprint). It also unblocks design decisions in S1192-S1200 (branding upload, domain bind, admin gate, feature gate, invoice, SLA, exit, case study, launch).

## Single-tenancy today

Today's data model is implicitly single-tenant:

- `news_sources` — global; one curated source pool.
- `posts` — global; one stream all tenants would share.
- `members` — single-tenant; one realm of users.
- `settings` — global key-value (`grimba_*` namespace).
- `users` — Botble admin auth, single realm.

## Multi-tenant principles for v2

1. **Shared core ingest, per-tenant editorial overlay.** Source pool + ingest pipeline + dedup + cluster + translate + NobuAI stays singleton (compute-efficient + content quality consistent). Tenants get per-tenant: featured-rails, brand chrome, custom domains, custom subset of categories visible, ad-tag overrides.
2. **Tenant = brand, not data fortress.** OEM use case is "Brand X wants GrimbaNews engine + their colors on their domain serving their audience" — not "Brand X needs data isolation." A future enterprise-tier (S1991) would tighten this if needed.
3. **No tenant-scoped breach blast radius.** All tenants share core DB; a breach affecting one == affecting all. Document this clearly in OEM contract (S1992 SLA).

## Schema draft

### Table `tenants`

```sql
CREATE TABLE tenants (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT NOT NULL UNIQUE,                    -- e.g. 'acme-news'
    display_name TEXT NOT NULL,                   -- e.g. 'Acme News'
    primary_domain TEXT NOT NULL UNIQUE,          -- e.g. 'acmenews.com'
    status TEXT NOT NULL DEFAULT 'active' CHECK(status IN ('trial', 'active', 'suspended', 'archived')),
    plan_tier TEXT NOT NULL DEFAULT 'starter' CHECK(plan_tier IN ('starter', 'pro', 'enterprise')),
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    archived_at DATETIME
);
```

### Table `tenant_domains`

```sql
CREATE TABLE tenant_domains (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    hostname TEXT NOT NULL UNIQUE,                -- e.g. 'acmenews.com', 'm.acmenews.com'
    is_primary BOOLEAN NOT NULL DEFAULT 0,
    ssl_status TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
CREATE INDEX idx_tenant_domains_tenant_id ON tenant_domains(tenant_id);
```

### Table `tenant_branding`

```sql
CREATE TABLE tenant_branding (
    tenant_id INTEGER PRIMARY KEY,
    logo_url TEXT,
    favicon_url TEXT,
    primary_color TEXT,
    secondary_color TEXT,
    font_family TEXT,
    nav_overrides_json TEXT,                      -- which rails, in what order
    footer_html TEXT,
    custom_css TEXT,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### Table `tenant_settings`

```sql
CREATE TABLE tenant_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    setting_key TEXT NOT NULL,
    setting_value TEXT,
    UNIQUE (tenant_id, setting_key),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

Mirror of current global `settings` table but per-tenant. Read-resolution chain: tenant_settings.value > settings.value > config default.

### Table `tenant_feature_flags`

```sql
CREATE TABLE tenant_feature_flags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    flag_key TEXT NOT NULL,
    enabled BOOLEAN NOT NULL DEFAULT 0,
    UNIQUE (tenant_id, flag_key),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

Enables S1195 feature-gate band — per-tenant access to: paid-tier features, API access, custom-rails, premium-translation, premium-NobuAI.

### Table `tenant_admins`

```sql
CREATE TABLE tenant_admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('owner', 'editor', 'viewer')),
    created_at DATETIME NOT NULL,
    UNIQUE (tenant_id, user_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

Scopes Botble admin auth (S1194 band) — a `users` row can be admin of multiple tenants with different roles. Iboga-operator users get role `owner` of a special "system" tenant.

### Optional: per-tenant content scoping

For the rare cases where a tenant wants per-tenant **content overlay** (e.g. their own editorial inserts, not shared posts), add:

```sql
CREATE TABLE tenant_post_visibility (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    post_id INTEGER NOT NULL,
    visible BOOLEAN NOT NULL DEFAULT 1,
    custom_lede TEXT,
    UNIQUE (tenant_id, post_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);
```

Per-tenant hide-from-this-tenant or custom lede; default = visible.

## Routing layer changes

- New middleware `App\Http\Middleware\GrimbaTenantResolve` — runs early; matches `$request->getHost()` against `tenant_domains.hostname`; injects resolved `Tenant` into request lifecycle via `app()->instance(Tenant::class, ...)`.
- Existing `GrimbaLocaleEnforce` middleware reads tenant default locale.
- Existing `GrimbaPublicCache` middleware adds `Vary: Host` to prevent cross-tenant cache poisoning.
- Setting helpers (`config('grimba.foo')` calls) wrap via `GrimbaSettings::for(tenant)->get('foo')` that resolves the chain.

## Migration approach (when first OEM signs)

1. **Phase 1** — create tables (5 minutes).
2. **Phase 2** — seed `tenants` row id=0 = "GrimbaNews canonical" with `primary_domain=grimbanews.com`. Backfill `tenant_domains` row. (10 minutes.)
3. **Phase 3** — wire `GrimbaTenantResolve` middleware; default behavior = tenant id=0 if no domain match (back-compat). (1-2 days.)
4. **Phase 4** — gradually wrap settings reads (per-Mythos sprint).
5. **Phase 5** — per-tenant branding overlay surface (S1192 ship).
6. **Phase 6** — per-tenant admin gate (S1194 ship).

## Things deliberately NOT in this schema

- **Per-tenant SQLite files** — would split data fortresses but kills shared ingest. Rejected.
- **Per-tenant DBs in MySQL / PostgreSQL** — same rejection; defers to multi-region (S1901) which is also deferred.
- **Per-tenant payment processor** — gates on S1196 OEM invoicing + S1841 PCI DSS re-scoping.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1191 row; cascades S1192-S1200)
- Sister docs: `docs/GRIMBANEWS_VENDOR_REGISTER.md` (vendor relationships will scale per-tenant), `docs/GRIMBANEWS_GDPR_ROPA.md` (Activity catalogue gains per-tenant fanout), `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md` (CC6 access controls scope)
- Existing settings infrastructure: Laravel `settings` table; helper at `app/Support/GrimbaLanguageSettings.php` is a per-domain example pattern
- Existing routing: `routes/web.php`, `platform/themes/echo/routes/web.php`
- Existing middleware: `app/Http/Middleware/GrimbaLocaleEnforce.php`, `app/Http/Middleware/GrimbaPublicCache.php`
