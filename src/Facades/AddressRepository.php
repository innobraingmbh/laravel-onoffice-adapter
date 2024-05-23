<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Facades\Testing\AddressRepositoryFake;
use Katalam\OnOfficeAdapter\Query\AddressBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\AddressRepository
 *
 * @method static AddressBuilder query()
 */
class AddressRepository extends Facade
{
    public static function fake(array ...$fakeResponses): AddressRepositoryFake
    {
        static::swap($fake = new AddressRepositoryFake(...$fakeResponses));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\AddressRepository::class;
    }
}
