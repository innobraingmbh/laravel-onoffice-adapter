<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Query\AddressBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\AddressRepository
 *
 * @method AddressBuilder query()
 */
class AddressRepository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\AddressRepository::class;
    }
}
