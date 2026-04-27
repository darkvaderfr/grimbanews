# GrimbaNews Admin Deployment Checklist

**Scope:** Later production deployment of the redesigned GrimbaNews backend.
**Status:** Prepared only. Production deployment is not run from this sprint.

## Pre-Deploy

- Confirm target commit is pushed to `origin/main`.
- Confirm `php artisan test` is green locally.
- Confirm `php artisan grimba:health` is green enough for editorial state.
- Confirm `php artisan grimba:nobuai-health` reports at least one LLM provider and the NobuTranslation chain.
- Confirm production API/provider budgets are ready before running `php artisan grimba:nobuai-health --live`.

## Deploy Order

1. Put the application in the normal deployment flow used for GrimbaNews.
2. Pull the target commit.
3. Run dependency/build steps already used by the host.
4. Clear Laravel and Botble caches.
5. Rebuild config/route/view caches if production uses cached artifacts.
6. Restart PHP workers if the host keeps long-running PHP processes.
7. Smoke `/admin/grimba/cockpit`, `/admin/grimba/translation`, `/admin/grimba/rss-drafts`, `/admin/grimba/news-sources/triage`, `/admin/grimba/coverage-map`.

## Cache Commands

```sh
php artisan optimize:clear
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

If production normally uses cached artifacts:

```sh
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Post-Deploy Smoke

- `php artisan grimba:health`
- `php artisan grimba:nobuai-health`
- `php artisan route:list --path=admin/grimba`
- Browser check: dark mode switch, sidebar readability, top dropdown opacity, provider vault readability, cockpit quick actions.

## Rollback

- Revert the deployment commit or redeploy the previous known-good commit.
- The redesign is CSS/Blade/docs/tests only in this phase; no destructive migration is required for rollback.
- If stale compiled views persist after rollback, run `php artisan optimize:clear`.
