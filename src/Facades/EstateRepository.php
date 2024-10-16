<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Facades;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Innobrain\OnOfficeAdapter\Query\EstateBuilder;
use Innobrain\OnOfficeAdapter\Query\EstateFileBuilder;
use Innobrain\OnOfficeAdapter\Query\EstatePictureBuilder;
use Innobrain\OnOfficeAdapter\Repositories\EstateRepository as RootRepository;

/**
 * @see RootRepository
 *
 * @method static EstateBuilder query()
 * @method static EstateFileBuilder files(int $estateId)
 * @method static EstatePictureBuilder pictures(int|array $estateId)
 */
class EstateRepository extends BaseRepository
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
