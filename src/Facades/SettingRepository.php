<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Facades\Testing\SettingRepositoryFake;
use Katalam\OnOfficeAdapter\Query\ActionBuilder;
use Katalam\OnOfficeAdapter\Query\ImprintBuilder;
use Katalam\OnOfficeAdapter\Query\RegionBuilder;
use Katalam\OnOfficeAdapter\Query\UserBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\SettingRepository
 *
 * @method static UserBuilder users()
 * @method static RegionBuilder regions()
 * @method static ImprintBuilder imprint()
 * @method static ActionBuilder actions()
 */
class SettingRepository extends Facade
{
    public static function fake(array ...$fakeResponses): SettingRepositoryFake
    {
        static::swap($fake = new SettingRepositoryFake(...$fakeResponses));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\SettingRepository::class;
    }
}
