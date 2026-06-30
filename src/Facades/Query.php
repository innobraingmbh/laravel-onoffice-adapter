<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Facades;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Innobrain\OnOfficeAdapter\Query\Builder;
use Innobrain\OnOfficeAdapter\Query\PendingBatch;
use Innobrain\OnOfficeAdapter\Repositories\BatchRepository as RootRepository;

/**
 * @see RootRepository
 */
class Query extends BaseRepository
{
    /**
     * Start a batch of requests that will be sent in a single API call.
     *
     * Each request becomes one batch action and returns a single, non-paginated
     * page: the first page only, capped at 500 records per action. If you need
     * every matching record, query that resource through its own repository
     * with get() instead of batching it.
     *
     * @param  array<int, OnOfficeRequest|Builder>  $requests
     */
    public static function batch(array $requests = []): PendingBatch
    {
        /** @var RootRepository $repository */
        $repository = static::getFacadeRoot();

        return $repository->batch($requests);
    }

    /**
     * @param  OnOfficeResponsePage|OnOfficeResponse|array<int, OnOfficeResponsePage|OnOfficeResponse|array<int, OnOfficeResponsePage>>|null  $stubCallables
     */
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
