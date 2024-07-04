<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Facades\Testing\FieldRepositoryFake;
use Katalam\OnOfficeAdapter\Query\FieldBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\FieldRepository
 *
 * @method static FieldBuilder query()
 */
class FieldRepository extends Facade
{
    public static function fake(array ...$fakeResponses): FieldRepositoryFake
    {
        static::swap($fake = new FieldRepositoryFake(...$fakeResponses));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\FieldRepository::class;
    }
}
