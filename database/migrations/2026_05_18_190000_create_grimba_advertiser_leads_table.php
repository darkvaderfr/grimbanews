<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * S-ADS-02 (Vader 2026-05-18) — sponsor lead capture.
 *
 * Today /advertise's CTA is a `mailto:` link. Mythos master fleet
 * R5 + R6 (2026-05-18) flagged that leads either don't arrive at
 * all (mail client missing) or land in an inbox with no admin index.
 * This table backs the real lead form S-ADS-04 ships, and the
 * admin index in S-ADS-06.
 *
 * Honeypot is enforced at the controller layer; this schema just
 * stores what gets through.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grimba_advertiser_leads', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191);
            $table->string('company', 191)->nullable();
            $table->string('budget_band', 32)->nullable();
            $table->text('goals')->nullable();
            $table->string('source_referrer', 512)->nullable();
            $table->string('source_slot', 64)->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('status', 24)->default('new');
            $table->timestampsTz();

            $table->index('email');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grimba_advertiser_leads');
    }
};
