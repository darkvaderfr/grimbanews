<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('newsletter_subscriptions', 'reader_bias_left')) {
                $table->unsignedSmallInteger('reader_bias_left')->default(0)->after('source_key');
                $table->unsignedSmallInteger('reader_bias_center')->default(0)->after('reader_bias_left');
                $table->unsignedSmallInteger('reader_bias_right')->default(0)->after('reader_bias_center');
                $table->unsignedSmallInteger('reader_bias_unknown')->default(0)->after('reader_bias_right');
                $table->string('digest_variant', 40)->nullable()->after('reader_bias_unknown');
            }
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
            foreach (['digest_variant', 'reader_bias_unknown', 'reader_bias_right', 'reader_bias_center', 'reader_bias_left'] as $column) {
                if (Schema::hasColumn('newsletter_subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
