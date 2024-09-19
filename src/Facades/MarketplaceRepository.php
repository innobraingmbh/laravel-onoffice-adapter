<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Facades;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Innobrain\OnOfficeAdapter\Query\MarketplaceBuilder;
use Innobrain\OnOfficeAdapter\Repositories\MarketplaceRepository as RootRepository;

/**
 * @see RootRepository
 *
 * @method static MarketplaceBuilder query()
 */
class MarketplaceRepository extends BaseRepository
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
