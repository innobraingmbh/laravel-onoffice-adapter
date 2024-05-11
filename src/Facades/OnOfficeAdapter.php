<?php

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Katalam\OnOfficeAdapter\OnOfficeAdapter
 */
class OnOfficeAdapter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\OnOfficeAdapter::class;
    }
}
