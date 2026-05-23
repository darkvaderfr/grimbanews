# GrimbaNews — Per-Visitor Consent Log Design

**Status:** schema + endpoint design (no implementation; ready to ship as a small sprint)
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps)
**Walks:** Mythos S1864 (per-visitor consent log) deferred → partial
**Gating dependency:** None — design is implementable today. Doc + design walk it from deferred (no design) to partial (design ready for ship sprint).

## Why this exists

S1864 was honest-deferred as "current /cookie-consent/{accept|reject} endpoint sets cookie + returns 204 with no DB write. ConsentMo / OneTrust-style consent-log table deferred." The reason it was deferred is implementation time, not external dependency — the table + endpoint + retention rules are all in our hands. Shipping the **design** is the partial step; the **migration + controller edit** is the next sprint.

## Schema design

```sql
CREATE TABLE consent_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_type TEXT NOT NULL CHECK(event_type IN ('accept', 'reject', 'withdraw', 'banner_dismissed')),
    consent_version TEXT NOT NULL,
    ip_hash TEXT NOT NULL,
    user_agent_hash TEXT NOT NULL,
    locale TEXT,
    member_id INTEGER,
    categories_json TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL
);

CREATE INDEX idx_consent_log_ip_hash ON consent_log(ip_hash);
CREATE INDEX idx_consent_log_member_id ON consent_log(member_id);
CREATE INDEX idx_consent_log_created_at ON consent_log(created_at);
```

Fields rationale:

- **`event_type`** — covers accept, reject, withdraw (post-consent revocation), banner_dismissed (silent close = no consent).
- **`consent_version`** — semver-style string ("1.0", "1.1", "2.0") tied to the cookie banner copy version. When banner copy changes, version bumps + re-prompt.
- **`ip_hash`** — HMAC-SHA256 of source IP via existing `GrimbaVaultEvents::ipHash()` helper. Privacy-by-design; allows auditor to verify "yes, this IP-class consented at this time" without storing raw IP.
- **`user_agent_hash`** — HMAC-SHA256 of UA string for ambiguity-reduction (same IP, different device).
- **`locale`** — which locale's banner copy was shown (for per-locale consent obligations).
- **`member_id`** — nullable; populated for logged-in members; NULL for anonymous visitors.
- **`categories_json`** — when per-category consent (S1865) ships, JSON like `{"functional": true, "analytics": true, "advertising": false}`. NULL for current binary accept/reject.

## Endpoint changes

File: `routes/web.php` (existing route) + `App\Http\Controllers\CookieConsentController` (new — currently a closure).

```php
// Before (current state):
Route::post('/cookie-consent/accept', fn (Request $r) => response()->noContent()->cookie('grimba_cookie_consent', 'accept', 525600));

// After:
Route::post('/cookie-consent/accept', [CookieConsentController::class, 'accept']);
Route::post('/cookie-consent/reject', [CookieConsentController::class, 'reject']);
Route::post('/cookie-consent/withdraw', [CookieConsentController::class, 'withdraw']);  // S1866 surrogate
```

Controller body (new file `app/Http/Controllers/CookieConsentController.php`):

```php
public function accept(Request $request): Response
{
    DB::table('consent_log')->insert([
        'event_type' => 'accept',
        'consent_version' => config('grimba.cookie_consent_version', '1.0'),
        'ip_hash' => GrimbaVaultEvents::ipHash($request->ip()),
        'user_agent_hash' => hash_hmac('sha256', $request->userAgent() ?? '', config('app.key')),
        'locale' => app()->getLocale(),
        'member_id' => auth()->id(),
        'categories_json' => null,  // until S1865
        'created_at' => now(),
    ]);

    return response()->noContent()->cookie(
        'grimba_cookie_consent',
        'accept',
        525600,
        '/',
        null,
        config('session.secure', true),
        true,
        false,
        'Lax'
    );
}

// reject() + withdraw() mirror the structure, different event_type.
```

## Retention rule

- Live table: 7 years (matches GDPR documentation requirements + common SOC 2 audit window).
- Archive: weekly to gzipped JSON in `storage/app/grimba-consent-archive/YYYY-WW.json.gz`.
- Archive retention: indefinite (small disk footprint; consent evidence is load-bearing for regulator inquiries).

Hook into existing `app/Console/Commands/GrimbaArchiveVaultEvents.php` pattern — add a sibling `GrimbaArchiveConsentLog` command + register in `routes/console.php` weekly Sunday 04:30.

## Counter on /admin

Surface a per-week / per-locale / per-version consent rate (accept count / total events) on `/admin/grimba/cockpit` (gated by Botble admin auth). Important for S1869 privacy-program metrics dashboard.

## Test plan

New test: `tests/Feature/ConsentLogTest.php`

- `test_accept_endpoint_writes_consent_log_row`
- `test_reject_endpoint_writes_consent_log_row_with_correct_event_type`
- `test_consent_log_row_uses_ip_hash_not_raw_ip` — privacy assertion
- `test_consent_log_archives_weekly`
- `test_consent_log_archive_retention_command_skips_rows_in_retention_window`

## Ship-sprint estimate

~3 hours: migration + controller + 2 console commands + tests + admin surface. Single sprint.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1864 row; gates for S1865, S1866, S1869)
- Existing endpoint: `routes/web.php` `/cookie-consent/{accept|reject}`
- Existing partial: `platform/themes/echo/partials/cookie-consent.blade.php`
- Privacy hashing helper: `app/Support/GrimbaVaultEvents.php::ipHash()`
- Sister archive command: `app/Console/Commands/GrimbaArchiveVaultEvents.php`
- GDPR record: `docs/GRIMBANEWS_GDPR_ROPA.md` (Activity 1)
- Standards reference: GDPR Article 7(1) demonstrable consent + EDPB Guidelines 05/2020 on consent
