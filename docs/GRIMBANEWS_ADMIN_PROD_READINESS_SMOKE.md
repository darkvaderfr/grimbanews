# GrimbaNews Admin Production Readiness Smoke

**Date:** 2026-04-27
**Scope:** Local verification only. No production deployment was run.
**Commit under test:** `1de435f`

## Commands

- `php artisan grimba:health` passed.
- `php artisan grimba:nobuai-health` passed.
- `php artisan route:list --path=admin/grimba` listed `52` Grimba admin routes.
- `php artisan test` passed with `50` tests and `754` assertions.

## Observed State

- Published posts: `583`
- Draft posts: `225`
- RSS feed health: `18` healthy, `0` wobbly, `0` sick, `5` inactive
- Duplicate-name groups: none
- NobuAI LLM provider: `openai`
- Translation chain: `nobutranslation`, `openai`, `googletx`
- Story insights: `1` ready, `26` pending

## Deployment Notes

- Production deploy is intentionally deferred.
- Before production deploy, run the same smoke after cache/build steps.
- A live provider call can be tested with `php artisan grimba:nobuai-health --live` when production keys and provider budgets are ready.
