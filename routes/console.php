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
