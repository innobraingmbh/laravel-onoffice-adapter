<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Facades\Testing\EstateRepositoryFake;
use Katalam\OnOfficeAdapter\Query\EstateBuilder;
use Katalam\OnOfficeAdapter\Query\EstateFileBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\EstateRepository
 *
 * @method static EstateBuilder query()
 * @method static EstateFileBuilder files(int $estateId)
 */
class EstateRepository extends Facade
{
    public static function fake(array ...$fakeResponses): EstateRepositoryFake
    {
        static::swap($fake = new EstateRepositoryFake(...$fakeResponses));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\EstateRepository::class;
    }
}
