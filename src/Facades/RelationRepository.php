<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Query\RelationBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\RelationRepository
 *
 * @method static RelationBuilder query()
 */
class RelationRepository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\RelationRepository::class;
    }
}
