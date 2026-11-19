<?php

namespace App\DTOs\Navigation;

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
