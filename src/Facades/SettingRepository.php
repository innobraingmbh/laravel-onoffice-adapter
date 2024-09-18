<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades;

use Katalam\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Katalam\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Katalam\OnOfficeAdapter\Query\ActionBuilder;
use Katalam\OnOfficeAdapter\Query\ImprintBuilder;
use Katalam\OnOfficeAdapter\Query\RegionBuilder;
use Katalam\OnOfficeAdapter\Query\UserBuilder;
use Katalam\OnOfficeAdapter\Repositories\SettingRepository as RootRepository;

/**
 * @see RootRepository
 *
 * @method static UserBuilder users()
 * @method static RegionBuilder regions()
 * @method static ImprintBuilder imprint()
 * @method static ActionBuilder actions()
 */
class SettingRepository extends BaseRepository
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
