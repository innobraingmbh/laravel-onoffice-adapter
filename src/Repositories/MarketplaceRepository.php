<?php

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\MarketplaceBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

readonly class MarketplaceRepository
{
    public function __construct(
        private OnOfficeService $onOfficeService,
    ) {
    }

    /**
     * Returns a new marketplace builder instance.
     */
    public function query(): MarketplaceBuilder
    {
        return new MarketplaceBuilder($this->onOfficeService);
    }
}
