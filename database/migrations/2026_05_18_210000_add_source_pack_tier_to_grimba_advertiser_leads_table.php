<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * S-ADS-11 (Vader 2026-05-18) — pack-tier telemetry.
 *
 * Records which pricing tier the lead originated from. The CTA on
 * each pricing pack scrolls to the lead form (Wave III); a small
 * JS hook copies the tier name into a hidden form input so the
 * controller persists it. Sales can then segment leads by tier
 * in the admin index (Wave AAA) — e.g. "30% of Editorial-tier
 * inquiries close vs 10% of Starter-tier."
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grimba_advertiser_leads', function (Blueprint $table) {
            $table->string('source_pack_tier', 64)->nullable()->after('source_slot');
        });
    }

    public function down(): void
    {
        Schema::table('grimba_advertiser_leads', function (Blueprint $table) {
            $table->dropColumn('source_pack_tier');
        });
    }
};
