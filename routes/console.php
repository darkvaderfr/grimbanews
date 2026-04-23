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
