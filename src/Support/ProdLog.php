<?php
declare(strict_types=1);

namespace App\Support;

use Cake\Log\Log;

final class ProdLog
{
    public static function info(string $event, array $ctx = []): void
    {
        Log::info(self::payload($event, $ctx));
    }

    public static function notice(string $event, array $ctx = []): void
    {
        Log::notice(self::payload($event, $ctx));
    }

    public static function warning(string $event, array $ctx = []): void
    {
        Log::warning(self::payload($event, $ctx));
    }

    public static function error(string $event, array $ctx = []): void
    {
        Log::error(self::payload($event, $ctx));
    }

    private static function payload(string $event, array $ctx): string
    {
        return json_encode(
            ['event' => $event, 'ts' => date('c')] + $ctx,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: $event;
    }
}
