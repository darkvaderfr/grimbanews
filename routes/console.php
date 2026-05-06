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

// GrimbaNews — nightly orphan-slug sweep so a post that got deleted
// (or a seed re-run) never leaves /blog/{slug} → 404 behind.
Schedule::command('grimba:cleanup-slugs')
    ->dailyAt('03:15')
    ->onOneServer()
    ->withoutOverlapping();

// GrimbaNews — RSS ingest. 30-minute cadence strikes the usual
// francophone publishing rhythm without hammering upstream feeds.
// withoutOverlapping protects against a slow run overlapping the
// next tick; runInBackground keeps artisan schedule:run itself snappy.
grimba_schedule_command('rss_ingest', 'grimba:poll-feeds')
    ->everyThirtyMinutes()
    ->onOneServer()
    ->withoutOverlapping(20)
    ->runInBackground();

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
// reader-facing stories get GroundNews-style analysis automatically.
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
// category for every configured country. Skips silently when the key
// isn't set; gated on the active toggle in /admin/grimba/newsapi.
grimba_schedule_command('newsapi_fetch', 'grimba:fetch-newsapi')
    ->cron('15 6,10,14,18,22 * * *')
    ->onOneServer()
    ->withoutOverlapping(20)
    ->runInBackground()
    ->when(fn () => (bool) setting('grimba_newsapi_active', true));

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
