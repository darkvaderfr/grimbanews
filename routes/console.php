<?php

use App\Support\GrimbaAutomationMonitor;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

if (! function_exists('grimba_schedule_command')) {
    function grimba_schedule_command(string $jobKey, string $command): \Illuminate\Console\Scheduling\Event
    {
        $runId = null;

        return Schedule::command($command)
            ->before(function () use ($jobKey, $command, &$runId): void {
                $runId = GrimbaAutomationMonitor::start($jobKey, $command);
            })
            ->onSuccess(function () use (&$runId): void {
                GrimbaAutomationMonitor::finish($runId, 'success', 0);
            })
            ->onFailure(function () use (&$runId): void {
                GrimbaAutomationMonitor::finish($runId, 'failed', 1, 'Scheduled command failed.');
            });
    }
}

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// GrimbaNews — daily SQLite .backup at 02:55 UTC (Wave YYYYYYYYYY).
// Closes the gap surfaced by the 2026-05-23 DR drill: the verifier
// was scheduled but no scheduled CREATE step existed, leaving
// database/backups/ empty unless an operator manually snapshotted.
// Now each night the live DB is VACUUM-into'd to a dated artifact,
// then the verifier runs 10 min later at 03:05 and reads it.
// Retention: --keep=14 prunes older artifacts (each ~20 MB).
grimba_schedule_command('backup_create', 'grimba:create-backup --keep=14')
    ->dailyAt('02:55')
    ->onOneServer()
    ->withoutOverlapping(20);

// GrimbaNews — daily backup restore smoke. Runs after create at 03:05
// so the just-created artifact is available. Verifier reads + opens +
// runs PRAGMA quick_check; alerts via GrimbaAutomationMonitor when
// the floor breaks.
grimba_schedule_command('backup_verify', 'grimba:verify-backups --min=1')
    ->dailyAt('03:05')
    ->onOneServer()
    ->withoutOverlapping(20);

// GrimbaNews — nightly orphan-slug sweep so a post that got deleted
// (or a seed re-run) never leaves /blog/{slug} → 404 behind.
Schedule::command('grimba:cleanup-slugs')
    ->dailyAt('03:15')
    ->onOneServer()
    ->withoutOverlapping();

// GrimbaNews — image proxy cache GC. Keeps publisher hero/logo cache
// from becoming another silent disk-pressure source on the shared VPS.
grimba_schedule_command('img_proxy_prune', 'grimba:prune-img-proxy-cache --days=60')
    ->dailyAt('03:25')
    ->onOneServer()
    ->withoutOverlapping(20);

// GrimbaNews — nightly origin-language backfill (S-LANG-04). Sweeps
// any post that still has original_language=NULL (typically very recent
// inserts where the ingest hook detector returned null on too-short
// text). Pure CPU, no upstream calls. Vader 2026-05-16.
grimba_schedule_command('lang_backfill', 'grimba:backfill-language')
    ->dailyAt('03:15')
    ->onOneServer()
    ->withoutOverlapping(20);

// GrimbaNews — daily dossier-language modal recompute (S-LANG-12).
// Sweeps story_clusters whose language_recomputed_at is older than 24h
// (or never set). Pure CPU, runs after lang_backfill so the modal
// gets the freshest tags. Vader 2026-05-17.
grimba_schedule_command('dossier_lang_recompute', 'grimba:recompute-dossier-language')
    ->dailyAt('03:45')
    ->onOneServer()
    ->withoutOverlapping(20);

// GrimbaNews — release evidence retention. Keeps the post-deploy proof
// trail durable without allowing tiny Markdown reports to grow forever.
grimba_schedule_command('release_evidence_prune', 'grimba:prune-release-evidence --days=30 --keep=30')
    ->dailyAt('03:35')
    ->onOneServer()
    ->withoutOverlapping(20);

// GrimbaNews — RSS ingest. 30-minute cadence strikes the usual
// francophone publishing rhythm without hammering upstream feeds.
// withoutOverlapping protects against a slow run overlapping the
// next tick; runInBackground keeps artisan schedule:run itself snappy.
grimba_schedule_command('rss_ingest', 'grimba:poll-feeds')
    ->everyThirtyMinutes()
    ->onOneServer()
    ->withoutOverlapping(20)
    ->runInBackground();

// GrimbaNews — live breaking/recent provider sweep. GDELT + Google News
// RSS are no-key and run by default; Webz.io News API Lite and paid
// providers such as Mediastack are wired but skipped until their keys
// are configured. This is
// separate from RSS so breaking rows and stale categories do not depend
// on publisher feeds alone.
grimba_schedule_command('breaking_live', 'grimba:fetch-breaking')
    ->cron('*/15 * * * *')
    ->onOneServer()
    ->withoutOverlapping(15)
    ->runInBackground()
    ->when(fn () => (bool) setting('grimba_breaking_active', true));

// GrimbaNews — dedicated newsdata.io lane. Off by default because the
// shared breaking_live cron already invokes newsdata.io through the
// provider list. Enable only when the free daily credit budget should
// be used more aggressively without increasing other provider calls.
grimba_schedule_command('breaking_newsdata', 'grimba:fetch-breaking --provider=newsdata-io')
    ->cron('*/8 * * * *')
    ->onOneServer()
    ->withoutOverlapping(8)
    ->runInBackground()
    ->when(fn () => (bool) setting('grimba_newsdata_io_dedicated_cron', false)
        && (bool) setting('grimba_breaking_active', true)
        && (bool) setting('grimba_newsdata_io_active', false));

// GrimbaNews — translation of un-translated posts. French and English
// queues run separately so the public language switch can serve both
// English-source articles in French and French-source articles in English.
// Safe no-op if no provider keys are configured; no cost incurred until
// a key is set.
grimba_schedule_command('translate_fr', 'grimba:translate-pending --to=fr --limit=50')
    ->cron('15,45 * * * *')
    ->onOneServer()
    ->withoutOverlapping(20)
    ->runInBackground();

grimba_schedule_command('translate_en', 'grimba:translate-pending --to=en --limit=50')
    ->cron('20,50 * * * *')
    ->onOneServer()
    ->withoutOverlapping(20)
    ->runInBackground();

// GrimbaNews — S-LSAT-11 (Vader 2026-05-18) — rule-driven auto-
// translate. Every 15 min, the rule engine picks high-views posts
// + force-both regions (default: africa) and translates them so a
// reader on the opposite locale gets coverage without manual
// editorial action. Bounded by the daily cap from
// `grimba_lang_rule_engine_daily_cap` (default 500/day). Safe
// no-op when `grimba_lang_rule_engine_enabled` is off.
grimba_schedule_command('translate_by_rule', 'grimba:translate-by-rule --limit=200')
    ->cron('*/15 * * * *')
    ->onOneServer()
    ->withoutOverlapping(20)
    ->runInBackground();

// GrimbaNews — auto-publish drafts from trusted classified sources
// (S150). Runs every 15 min, 1h after each :15/:45 ingest cadence so
// any new draft has settled before promotion. Editor still has the
// 1h age window (+ the cron's :00/:30 offset) to manually demote.
grimba_schedule_command('publish_trusted', 'grimba:publish-trusted')
    ->cron('5,35 * * * *')
    ->onOneServer()
    ->withoutOverlapping(15)
    ->runInBackground();

// GrimbaNews — editorial override buckets for drafts that fail the
// trusted auto-publish guardrails. They still publish, but under
// explicit review categories instead of silently staying in draft.
grimba_schedule_command('publish_guardrails', 'grimba:publish-guardrail-categories')
    ->cron('8,38 * * * *')
    ->onOneServer()
    ->withoutOverlapping(15)
    ->runInBackground();

// GrimbaNews — freshness watchdog. If the public feed ever drops
// below the daily publication floor, promote trusted recent drafts
// immediately and fail loudly when there is no healthy intake to use.
grimba_schedule_command('freshness_watchdog', 'grimba:ensure-daily-publish --min=12 --window-hours=24 --per-category-min=3 --category-window-hours=24 --categories=all --max-publish-per-category=5')
    ->cron('11,41 * * * *')
    ->onOneServer()
    ->withoutOverlapping(15);

// GrimbaNews — ops health guard. This fails into the automation
// monitor when intake, public freshness, or disk headroom drops below
// the operating floor, before readers have to report stale news.
grimba_schedule_command('ops_health', 'grimba:health --fail-on-risk --min-full-content-coverage=70 --min-category-published-24h=3 --category-freshness-scope=all')
    ->hourlyAt(27)
    ->onOneServer()
    ->withoutOverlapping(10);

// GrimbaNews — full article extraction for subscriber/member reading.
// Runs shortly after trusted auto-publish so newly public RSS/NewsAPI
// posts get a readable body without waiting for an editor.
grimba_schedule_command('full_articles', 'grimba:fetch-full-articles --limit=80')
    ->cron('12,42 * * * *')
    ->onOneServer()
    ->withoutOverlapping(25)
    ->runInBackground();

// GrimbaNews — NobuAI insight treatment for newly published story
// clusters. Runs after publish + full extraction + translation ticks so
// reader-facing stories get multi-source analysis automatically.
grimba_schedule_command('nobuai_summaries', 'grimba:nobuai-summaries --limit=80')
    ->cron('18,48 * * * *')
    ->onOneServer()
    ->withoutOverlapping(25)
    ->runInBackground();

// Refresh existing NobuAI insights when later coverage joins a cluster.
grimba_schedule_command('nobuai_stale', 'grimba:nobuai-summaries --stale --limit=25')
    ->cron('25,55 * * * *')
    ->onOneServer()
    ->withoutOverlapping(25)
    ->runInBackground();

// GrimbaNews — NewsAPI category sweeps (S128/S257). Runs five times
// per day and, on each sweep, fetches every configured NewsAPI
// category for every configured country. It only runs when NewsAPI is
// explicitly active or a key exists; active-without-key fails loudly
// through grimba:fetch-newsapi and grimba:health.
grimba_schedule_command('newsapi_fetch', 'grimba:fetch-newsapi')
    ->cron('15 6,10,14,18,22 * * *')
    ->onOneServer()
    ->withoutOverlapping(20)
    ->runInBackground()
    ->when(fn () => (bool) setting(
        'grimba_newsapi_active',
        trim((string) setting('grimba_newsapi_key', env('NEWSAPI_KEY', ''))) !== ''
    ));

// GrimbaNews — daily source classification audit/backfill. This keeps
// newly added RSS/NewsAPI sources from sitting as "unknown" and syncs
// source-level bias/factuality/ownership metadata to posts that still
// have missing provider fields.
grimba_schedule_command('source_classifier', 'grimba:classify-sources --apply --sync-posts --min-confidence=80')
    ->dailyAt('04:00')
    ->onOneServer()
    ->withoutOverlapping(30)
    ->runInBackground();

// GrimbaNews — weekly image-backfill sweep (S94). Catches two cases
// the live poller misses:
//   1. Older drafts (aged out of the feed window at the time of their
//      first ingest) that now have an og:image on the article page.
//   2. Drafts whose article page was down when ingested — many
//      publishers restore og:image within hours.
// Weekly cadence: one HTTP per still-image-less post is enough; more
// frequent doesn't pay. Runs Monday 04:10 (after the nightly slug
// sweep at 03:15) so the two batch jobs don't overlap.
Schedule::command('grimba:enrich-drafts')
    ->weeklyOn(1, '04:10')
    ->onOneServer()
    ->withoutOverlapping(60)
    ->runInBackground();

// GrimbaNews — privacy-preserving vault analytics archive. The live
// endpoint stores only event/post/timestamp/salted IP hash; this weekly
// job rewrites the current month's rollup for editorial trend review.
grimba_schedule_command('vault_events_archive', 'grimba:archive-vault-events')
    ->weeklyOn(1, '04:25')
    ->onOneServer()
    ->withoutOverlapping(30)
    ->runInBackground();

// GrimbaNews — member-only weekly saved-article digest. The public
// vault stays cookie-first; opted-in members sync their current vault
// IDs so the scheduler can email the saved article list once a week.
grimba_schedule_command('vault_digest_weekly', 'grimba:vault-digests')
    ->weeklyOn(1, '04:40')
    ->onOneServer()
    ->withoutOverlapping(30)
    ->runInBackground();

// GrimbaNews — member-only saved-search alerts. Readers can follow
// a query/facet combo from /search; this weekly digest sends only
// articles published after the search was saved or last emailed.
grimba_schedule_command('saved_search_digest_weekly', 'grimba:saved-search-digests')
    ->weeklyOn(1, '04:55')
    ->onOneServer()
    ->withoutOverlapping(30)
    ->runInBackground();
