<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

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
Schedule::command('grimba:poll-feeds')
    ->everyThirtyMinutes()
    ->onOneServer()
    ->withoutOverlapping(20)
    ->runInBackground();

// GrimbaNews — translation of un-translated posts. French and English
// queues run separately so the public language switch can serve both
// English-source articles in French and French-source articles in English.
// Safe no-op if no provider keys are configured; no cost incurred until
// a key is set.
Schedule::command('grimba:translate-pending --to=fr --limit=50')
    ->cron('15,45 * * * *')
    ->onOneServer()
    ->withoutOverlapping(20)
    ->runInBackground();

Schedule::command('grimba:translate-pending --to=en --limit=50')
    ->cron('20,50 * * * *')
    ->onOneServer()
    ->withoutOverlapping(20)
    ->runInBackground();

// GrimbaNews — auto-publish drafts from trusted classified sources
// (S150). Runs every 15 min, 1h after each :15/:45 ingest cadence so
// any new draft has settled before promotion. Editor still has the
// 1h age window (+ the cron's :00/:30 offset) to manually demote.
Schedule::command('grimba:publish-trusted')
    ->cron('5,35 * * * *')
    ->onOneServer()
    ->withoutOverlapping(15)
    ->runInBackground();

// GrimbaNews — full article extraction for subscriber/member reading.
// Runs shortly after trusted auto-publish so newly public RSS/NewsAPI
// posts get a readable body without waiting for an editor.
Schedule::command('grimba:fetch-full-articles --limit=40')
    ->cron('12,42 * * * *')
    ->onOneServer()
    ->withoutOverlapping(25)
    ->runInBackground();

// GrimbaNews — NobuAI insight treatment for newly published story
// clusters. Runs after publish + full extraction + translation ticks so
// reader-facing stories get GroundNews-style analysis automatically.
Schedule::command('grimba:nobuai-summaries --limit=40')
    ->cron('18,48 * * * *')
    ->onOneServer()
    ->withoutOverlapping(25)
    ->runInBackground();

// Refresh existing NobuAI insights when later coverage joins a cluster.
Schedule::command('grimba:nobuai-summaries --stale --limit=25')
    ->cron('25,55 * * * *')
    ->onOneServer()
    ->withoutOverlapping(25)
    ->runInBackground();

// GrimbaNews — NewsAPI ingest (S128). Runs at :15 and :45 past the
// hour so it doesn't collide with the RSS poller (which fires on the
// :00 / :30 boundary). Skips silently when the key isn't set; gated
// on the active toggle in /admin/grimba/newsapi.
Schedule::command('grimba:fetch-newsapi')
    ->cron('15,45 * * * *')
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
