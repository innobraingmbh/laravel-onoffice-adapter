<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Facades;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Innobrain\OnOfficeAdapter\Query\FieldBuilder;
use Innobrain\OnOfficeAdapter\Repositories\FieldRepository as RootRepository;

/**
 * @see RootRepository
 *
 * @method static FieldBuilder query()
 */
class FieldRepository extends BaseRepository
{
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
