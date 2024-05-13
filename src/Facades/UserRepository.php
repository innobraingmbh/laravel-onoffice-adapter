<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Facades\Testing\UserRepositoryFake;
use Katalam\OnOfficeAdapter\Query\UserBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\UserRepository
 *
 * @method UserBuilder query()
 */
class UserRepository extends Facade
{
    public static function fake(array ...$fakeResponses): UserRepositoryFake
    {
        static::swap($fake = new UserRepositoryFake(...$fakeResponses));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\UserRepository::class;
    }
}
