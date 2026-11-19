<?php

namespace App\DTOs\Navigation;

use App\Enums\Icon;

/**
 * Satu item menu yang sudah siap dirender: URL dan state aktif telah diselesaikan
 * oleh App\Services\NavigationService, sehingga view tidak menjalankan logika apa pun.
 */
final readonly class NavItemData
{
    public function __construct(
        public string $label,
        public Icon $icon,
        public string $url,
        public bool $active,
    ) {}
}
