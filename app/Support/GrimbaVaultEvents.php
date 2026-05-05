<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GrimbaVaultEvents
{
    public const TABLE = 'vault_events';

    public static function record(Request $request, string $event, int $postId): bool
    {
        if (! in_array($event, ['save', 'unsave'], true) || $postId <= 0) {
            return false;
        }

        if (! Schema::hasTable(self::TABLE)) {
            return false;
        }

        try {
            DB::table(self::TABLE)->insert([
                'event' => $event,
                'post_id' => $postId,
                'ts' => now(),
                'ip_hash' => self::ipHash((string) $request->ip()),
            ]);
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    public static function ipHash(string $ip): string
    {
        $salt = (string) config('app.key', 'grimbanews-vault-events');

        return hash_hmac('sha256', $ip, $salt !== '' ? $salt : 'grimbanews-vault-events');
    }
}
