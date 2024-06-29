<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\MarketplaceBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class MarketplaceRepository
{
    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {}

    /**
     * Returns a new marketplace builder instance.
     */
    public function query(): MarketplaceBuilder
    {
        return new MarketplaceBuilder($this->onOfficeService);
    }
}
