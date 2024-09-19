<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\MarketplaceBuilder;

class MarketplaceRepository extends BaseRepository
{
    protected function createBuilder(): MarketplaceBuilder
    {
        return new MarketplaceBuilder;
    }
}
