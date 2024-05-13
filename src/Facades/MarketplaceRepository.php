<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Query\MarketplaceBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\MarketplaceRepository
 *
 * @method MarketplaceBuilder query()
 */
class MarketplaceRepository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\MarketplaceRepository::class;
    }
}
