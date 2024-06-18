<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Facades\Testing\AddressRepositoryFake;
use Katalam\OnOfficeAdapter\Query\ActivityBuilder;
use Katalam\OnOfficeAdapter\Query\AddressBuilder;
use Katalam\OnOfficeAdapter\Query\Testing\ActivityBuilderFake;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\ActivityRepository
 *
 * @method static ActivityBuilder query()
 */
class ActivityRepository extends Facade
{
    public static function fake(array ...$fakeResponses): ActivityBuilderFake
    {
        static::swap($fake = new ActivityBuilderFake(...$fakeResponses));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\ActivityRepository::class;
    }
}
