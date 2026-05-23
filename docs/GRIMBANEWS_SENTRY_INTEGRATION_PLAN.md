# GrimbaNews — Sentry Integration Plan

**Status:** plan / pre-engagement (no Sentry account)
**Owner:** Jacob Lee (DevOps)
**Walks:** Mythos S1013 (Sentry routing) + S1012 (JS error budget) deferred → partial
**Gating dependency:** Sentry Team plan ($26/mo per project) not yet provisioned. This document is the **integration-point map + 30-line wiring sample** that activation will follow.

## Why this exists

S1013 was honest-deferred because no Sentry account exists. S1012 (JS error budget) cascades on it — the JS-side `window.onerror` capture has no destination until Sentry (or equivalent) is wired. Today, errors land in `storage/logs/laravel.log` via `app/Exceptions/Handler.php`; nothing aggregates them across requests or releases.

This plan identifies the two integration points (Laravel server-side + JS client-side), provides the 30-line code samples that will be applied on day one, and lists the operational additions (release tagging, sample rate, PII scrubbing).

## Vendor decision

| Vendor | Pricing | Why |
|---|---|---|
| **Sentry Team** | $26/mo for 50k events | Industry standard; first-class Laravel + JS SDK; integrates with our git release flow via `sentry-cli releases new` |
| Bugsnag | $59/mo | More expensive, smaller community |
| Self-hosted GlitchTip | Free + VPS cost | Hosting policy says VPS-only for apps without dedicated hosting — adds maintenance burden |

**Recommendation:** Sentry Team — $26/mo, one bill across GrimbaNews + future Iboga products.

## Server-side integration point (S1013)

File: `app/Exceptions/Handler.php`

```php
// composer require sentry/sentry-laravel
// php artisan sentry:publish --dsn=https://<key>@<org>.ingest.sentry.io/<project>

public function register(): void
{
    $this->reportable(function (Throwable $e) {
        if (app()->bound('sentry') && $this->shouldReport($e)) {
            app('sentry')->captureException($e);
        }
    });
}
```

Plus `config/sentry.php` — sample rate 1.0 in pre-launch, 0.2 once traffic ramps; release tag from `git describe --tags --always` baked into the deploy script.

## JS-side integration point (S1012)

File: `platform/themes/echo/layouts/grimba-chrome.blade.php` (head section, before existing inline scripts)

```html
<script src="https://browser.sentry-cdn.com/7.x/bundle.tracing.min.js" crossorigin="anonymous"></script>
<script>
  Sentry.init({
    dsn: "{{ config('services.sentry.frontend_dsn') }}",
    release: "{{ config('app.release') }}",
    environment: "{{ app()->environment() }}",
    tracesSampleRate: 0.1,
    beforeSend(event) {
      // strip cookies + member ids per PII policy (GDPR-safe)
      if (event.request) delete event.request.cookies;
      return event;
    }
  });
</script>
```

The `beforeSend` hook is load-bearing — without it the GDPR DPIA (S1851 / `docs/GRIMBANEWS_GDPR_ROPA.md`) would have to expand scope to cover Sentry as a processor of cookie + member data.

## PII scrubbing rules

- Strip `Cookie` header (Laravel session, XSRF-TOKEN, grimba_lang, grimba_cookie_consent, grimba_for_you_recent).
- Strip `Authorization` header.
- Drop request body for `/cookie-consent/*`, `/api/contact`, `/admin/*`.
- Hash `request.user.ip_address` via the same `GrimbaVaultEvents::ipHash()` HMAC-SHA256 helper (`app/Support/GrimbaVaultEvents.php`).

## Release tagging

Add to deploy script (per `bin/grimba-deploy.sh` if/when adopted):

```bash
RELEASE=$(git describe --tags --always)
sentry-cli releases new "$RELEASE"
sentry-cli releases set-commits "$RELEASE" --auto
sentry-cli releases finalize "$RELEASE"
```

This ties each Sentry event to the deploy that caused it — load-bearing for the day-1 / day-7 / day-30 retros (S1002-S1004).

## Activation checklist (day-1 when account ships)

1. Provision Sentry Team account on `iboga-ventures` org.
2. Create two projects: `grimbanews-backend` (PHP/Laravel) + `grimbanews-frontend` (JavaScript browser).
3. Add DSNs to `.env` + `.env.example` (`SENTRY_LARAVEL_DSN`, `SENTRY_FRONTEND_DSN`).
4. `composer require sentry/sentry-laravel` + `php artisan sentry:publish`.
5. Apply the two code blocks above.
6. Add release tagging to deploy script.
7. Verify with a synthetic `throw new \RuntimeException('sentry-test')` and a synthetic JS `throw new Error('sentry-test')`.
8. Update `docs/GRIMBANEWS_GDPR_ROPA.md` to add Sentry as processor.
9. Update `docs/GRIMBANEWS_VENDOR_REGISTER.md` row for Sentry.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1012, S1013 rows)
- Server integration point: `app/Exceptions/Handler.php`
- JS integration point: `platform/themes/echo/layouts/grimba-chrome.blade.php`
- PII hashing helper: `app/Support/GrimbaVaultEvents.php::ipHash()`
- Vendor register update target: `docs/GRIMBANEWS_VENDOR_REGISTER.md`
- GDPR DPIA update target: `docs/GRIMBANEWS_GDPR_ROPA.md`
