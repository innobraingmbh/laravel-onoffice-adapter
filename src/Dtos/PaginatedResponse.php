<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Dtos;

use Illuminate\Support\Collection;

readonly class PaginatedResponse
{
    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    public function __construct(
        public Collection $items,
        public int $total,
    ) {}
}
