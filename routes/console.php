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

// GrimbaNews — translation of un-translated posts. Runs 15 min after
// each poll-feeds tick so new drafts get their French translation
// before an editor opens the queue. Safe no-op if no provider keys
// are configured; no cost incurred until a key is set.
Schedule::command('grimba:translate-pending --limit=50')
    ->cron('15,45 * * * *')
    ->onOneServer()
    ->withoutOverlapping(20)
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
