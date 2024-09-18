<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\MarketplaceBuilder;

class MarketplaceRepository extends BaseRepository
{
    protected function createBuilder(): MarketplaceBuilder
    {
        return new MarketplaceBuilder;
    }
}
