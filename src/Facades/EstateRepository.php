<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\EstateRepository
 * @method static all()
 */
class EstateRepository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\EstateRepository::class;
    }
}
