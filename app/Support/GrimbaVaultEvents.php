<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GrimbaVaultEvents
{
    public const TABLE = 'vault_events';
    public const EVENT_SAVE = 'save';
    public const EVENT_UNSAVE = 'unsave';
    public const EVENT_RETURN_VISIT = 'return_visit';

    public static function record(Request $request, string $event, int $postId = 0): bool
    {
        if (! in_array($event, [self::EVENT_SAVE, self::EVENT_UNSAVE, self::EVENT_RETURN_VISIT], true)) {
            return false;
        }

        if ($event === self::EVENT_RETURN_VISIT) {
            $postId = 0;
        }

        if (in_array($event, [self::EVENT_SAVE, self::EVENT_UNSAVE], true) && $postId <= 0) {
            return false;
        }

        if (! Schema::hasTable(self::TABLE)) {
            return false;
        }

        $ipHash = self::ipHash((string) $request->ip());

        try {
            DB::table(self::TABLE)->insert([
                'event' => $event,
                'post_id' => $postId,
                'ts' => now(),
                'ip_hash' => $ipHash,
            ]);
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    public static function recordReturnVisit(Request $request): bool
    {
        if (! Schema::hasTable(self::TABLE)) {
            return false;
        }

        $ipHash = self::ipHash((string) $request->ip());

        $alreadyRecordedToday = DB::table(self::TABLE)
            ->where('event', self::EVENT_RETURN_VISIT)
            ->where('ip_hash', $ipHash)
            ->where('ts', '>=', now()->startOfDay())
            ->exists();

        if ($alreadyRecordedToday) {
            return true;
        }

        return self::record($request, self::EVENT_RETURN_VISIT);
    }

    public static function ipHash(string $ip): string
    {
        $salt = (string) config('app.key', 'grimbanews-vault-events');

        return hash_hmac('sha256', $ip, $salt !== '' ? $salt : 'grimbanews-vault-events');
    }
}
