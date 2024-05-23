<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Query\FieldBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\FieldRepository
 *
 * @method static FieldBuilder query()
 */
class FieldRepository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\FieldRepository::class;
    }
}
