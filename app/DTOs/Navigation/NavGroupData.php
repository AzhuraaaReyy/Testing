<?php

namespace App\DTOs\Navigation;

/**
 * Satu grup menu Sidebar portal (judul opsional + item).
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → interface NavGroupData.
 */
final readonly class NavGroupData
{
    /**
     * @param  list<NavItemData>  $items
     */
    public function __construct(
        public ?string $label,
        public array $items,
    ) {}
}
