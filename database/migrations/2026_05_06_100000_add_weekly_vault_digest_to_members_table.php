<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('members')) {
            return;
        }

        Schema::table('members', function (Blueprint $table): void {
            if (! Schema::hasColumn('members', 'weekly_vault_digest')) {
                $table->boolean('weekly_vault_digest')->default(false)->after('status');
            }

            if (! Schema::hasColumn('members', 'vault_digest_post_ids')) {
                $table->text('vault_digest_post_ids')->nullable()->after('weekly_vault_digest');
            }

            if (! Schema::hasColumn('members', 'vault_digest_synced_at')) {
                $table->timestamp('vault_digest_synced_at')->nullable()->after('vault_digest_post_ids');
            }

            if (! Schema::hasColumn('members', 'vault_digest_sent_at')) {
                $table->timestamp('vault_digest_sent_at')->nullable()->after('vault_digest_synced_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('members')) {
            return;
        }

        Schema::table('members', function (Blueprint $table): void {
            foreach (['vault_digest_sent_at', 'vault_digest_synced_at', 'vault_digest_post_ids', 'weekly_vault_digest'] as $column) {
                if (Schema::hasColumn('members', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
