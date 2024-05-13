<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Query\UserBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\UserRepository
 *
 * @method UserBuilder query()
 */
class UserRepository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\UserRepository::class;
    }
}
