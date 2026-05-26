# GrimbaNews — Secret Rotation Runbook

**Status:** runbook v0
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps)
**Walks:** Mythos S942 (secret rotation runbook) deferred → partial
**Gating dependency:** Vault or KMS for centralized secret store (operator-side; currently .env-driven).

## Secrets in scope

1. `APP_KEY` (Laravel encryption)
2. Database creds (SQLite file path + future MySQL creds)
3. SMTP credentials (Mailgun, SES)
4. NobuAI API keys (Anthropic, OpenAI, Mistral, OpenRouter)
5. NewsAPI keys (per-provider)
6. Slack webhook URL (GRIMBA_HEALTH_SLACK_WEBHOOK)
7. Stripe API keys (when subscriptions launch)
8. OAuth client secrets (Google, GitHub for admin SSO)
9. Backup encryption key (S945 future)
10. Admin operator credentials (Botble user passwords)

## Per-secret cadence

| Secret | Rotation cadence | Trigger |
|---|---|---|
| APP_KEY | Annual + on breach | calendar reminder + incident |
| DB creds | 90 days + on staff change | rotation cron OR HR event |
| SMTP creds | Annual + on provider breach | provider advisory |
| NobuAI keys | 60 days + on rate-limit ban | provider notification |
| NewsAPI keys | Annual + on breach | provider notification |
| Slack webhook | Annual + on team change | HR event |
| Stripe keys | Annual + on PCI scope event | PCI cycle |
| OAuth secrets | Annual + on app re-registration | provider event |
| Backup key | Annual + on staff change | HR event |
| Admin passwords | 90 days + on staff change | HR event |

## Rotation procedure (per secret type)

### APP_KEY
1. Generate new key locally: `php artisan key:generate --show`
2. Schedule maintenance window (15 min downtime).
3. Run a migration that re-encrypts any encrypted-cast columns with the new key BEFORE swap.
4. Update `.env` on VPS.
5. `php artisan config:clear && php artisan cache:clear`.
6. Smoke test: login, posts list, /health.
7. Old key kept in a sealed offline note for 30 days for emergency rollback.

### NobuAI keys (most common)
1. Operator opens new key in provider dashboard.
2. Updates `.env`.
3. `php artisan config:clear`.
4. `php artisan grimba:nobuai-smoke` confirms call succeeds.
5. Revoke old key in provider dashboard.

### Admin passwords
1. Operator triggers password reset in Botble admin.
2. New password sent via email.
3. Old password sessions revoked.

## Audit

- Last-rotated-at stored per secret in `secret_rotation_log.md` (operator-managed local file).
- Quarterly CISO review.

## Cross-references

Master plan: S942. Sister: `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`, `docs/GRIMBANEWS_DEPLOY_KEY_REVIEW_POLICY.md` (S946 — companion).
