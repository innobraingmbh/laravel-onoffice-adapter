<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Facades\Testing\ActivityRepositoryFake;
use Katalam\OnOfficeAdapter\Query\ActivityBuilder;
use Katalam\OnOfficeAdapter\Query\Testing\ActivityBuilderFake;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\ActivityRepository
 *
 * @method static ActivityBuilder query()
 */
class ActivityRepository extends Facade
{
    public static function fake(array ...$fakeResponses): ActivityRepositoryFake
    {
        static::swap($fake = new ActivityRepositoryFake(...$fakeResponses));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\ActivityRepository::class;
    }
}
