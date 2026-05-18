<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * S-ADS-07 (Vader 2026-05-18) — admin lead detail workflow.
 *
 * Wave ZZ shipped the lead capture (POST /advertise/leads).
 * Wave AAA shipped the admin index. This migration adds the
 * notes column the detail page (Wave LLL) writes to, plus a
 * timestamp recording when an op last updated the lead manually
 * so dashboards can surface staleness.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grimba_advertiser_leads', function (Blueprint $table) {
            $table->text('admin_notes')->nullable()->after('status');
            $table->timestampTz('last_admin_action_at')->nullable()->after('admin_notes');
        });
    }

    public function down(): void
    {
        Schema::table('grimba_advertiser_leads', function (Blueprint $table) {
            $table->dropColumn(['admin_notes', 'last_admin_action_at']);
        });
    }
};
