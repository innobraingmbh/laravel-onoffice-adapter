<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Facades\Testing\MarketplaceRepositoryFake;
use Katalam\OnOfficeAdapter\Query\MarketplaceBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\MarketplaceRepository
 *
 * @method MarketplaceBuilder query()
 */
class MarketplaceRepository extends Facade
{
    public static function fake(array ...$fakeResponses): MarketplaceRepositoryFake
    {
        static::swap($fake = new MarketplaceRepositoryFake(...$fakeResponses));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\MarketplaceRepository::class;
    }
}
