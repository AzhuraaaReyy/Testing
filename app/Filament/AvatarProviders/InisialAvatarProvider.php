<?php

namespace App\Filament\AvatarProviders;

use App\Models\User;
use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Avatar inisial yang dibuat server sebagai SVG (data: URI), pengganti UiAvatarsProvider bawaan
 * Filament (SEC2-08): nama/inisial dan IP Admin tidak lagi dikirim ke ui-avatars.com, dan CSP
 * tidak perlu mengizinkan domain pihak ketiga. Inisial & warna sama dengan avatar Navbar portal.
 */
final class InisialAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        $inisial = $record instanceof User
            ? $record->inisial
            : Str::of(Filament::getNameForDefaultAvatar($record))->explode(' ')->filter()->take(2)
                ->map(fn (string $kata): string => mb_strtoupper(mb_substr($kata, 0, 1)))->implode('');

        // Palet sama dengan primary panel (->colors(['primary' => Color::Teal]) di AdminPanelProvider).
        $latar = Color::convertToHex(Color::Teal[100]);
        $teks = Color::convertToHex(Color::Teal[800]);

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" fill="%s"/>'
            .'<text x="50%%" y="50%%" dy=".35em" text-anchor="middle" font-family="Roboto, Arial, sans-serif" font-size="26" font-weight="700" fill="%s">%s</text></svg>',
            $latar,
            $teks,
            htmlspecialchars($inisial, ENT_XML1 | ENT_QUOTES, 'UTF-8'),
        );

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
