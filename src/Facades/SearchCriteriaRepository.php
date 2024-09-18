<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades;

use Katalam\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Katalam\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Katalam\OnOfficeAdapter\Query\SearchCriteriaBuilder;
use Katalam\OnOfficeAdapter\Repositories\SearchCriteriaRepository as RootRepository;

/**
 * @see RootRepository
 *
 * @method static SearchCriteriaBuilder query()
 */
class SearchCriteriaRepository extends BaseRepository
{
    public static function fake(OnOfficeResponsePage|OnOfficeResponse|array|null $stubCallables): RootRepository
    {
        return tap(static::getFacadeRoot(), static function (RootRepository $fake) use ($stubCallables) {
            $fake->fake($stubCallables);
        });
    }

    protected static function getFacadeAccessor(): string
    {
        return RootRepository::class;
    }
}
