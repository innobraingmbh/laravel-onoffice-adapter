<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Facades\Testing\SearchCriteriaRepositoryFake;
use Katalam\OnOfficeAdapter\Query\SearchCriteriaBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\SearchCriteriaRepository
 *
 * @method static SearchCriteriaBuilder query()
 */
class SearchCriteriaRepository extends Facade
{
    public static function fake(array ...$fakeResponses): SearchCriteriaRepositoryFake
    {
        static::swap($fake = new SearchCriteriaRepositoryFake(...$fakeResponses));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\SearchCriteriaRepository::class;
    }
}
