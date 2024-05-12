<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Query\EstateBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\EstateRepository
 *
 * @method EstateBuilder query()
 */
class EstateRepository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\EstateRepository::class;
    }
}
