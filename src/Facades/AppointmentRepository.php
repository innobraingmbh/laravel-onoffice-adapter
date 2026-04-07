<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Facades;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Innobrain\OnOfficeAdapter\Query\AppointmentBuilder;
use Innobrain\OnOfficeAdapter\Repositories\AppointmentRepository as RootRepository;

/**
 * @see RootRepository
 *
 * @method static AppointmentBuilder query()
 */
class AppointmentRepository extends BaseRepository
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
