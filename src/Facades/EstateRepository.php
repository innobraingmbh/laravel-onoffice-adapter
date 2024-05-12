<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Query\EstateBuilder;
use Katalam\OnOfficeAdapter\Query\EstateFileBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\EstateRepository
 *
 * @method EstateBuilder query()
 * @method EstateFileBuilder files(int $estateId)
 */
class EstateRepository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\EstateRepository::class;
    }
}
