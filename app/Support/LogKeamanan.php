<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Pencatatan kejadian keamanan ke channel log "security" (SEC2-04, OWASP A09):
 * storage/logs/security-YYYY-MM-DD.log. Email disamarkan dan password tidak pernah dicatat.
 */
final class LogKeamanan
{
    public const CHANNEL = 'security';

    /**
     * @param  array<string, mixed>  $konteks
     */
    public static function catat(string $kejadian, array $konteks = [], string $level = 'warning'): void
    {
        Log::channel(self::CHANNEL)->log($level, $kejadian, $konteks);
    }

    /** "siswa@sekolah.test" → "si***@sekolah.test". */
    public static function samarkanEmail(string $email): string
    {
        $email = Str::lower(trim($email));
        $at = strrpos($email, '@');

        if ($at === false) {
            return Str::limit($email, 2, '***');
        }

        return Str::substr($email, 0, min(2, $at)).'***'.Str::substr($email, $at);
    }
}
