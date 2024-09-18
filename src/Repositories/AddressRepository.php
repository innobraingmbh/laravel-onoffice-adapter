<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\AddressBuilder;

class AddressRepository extends BaseRepository
{
    protected function createBuilder(): AddressBuilder
    {
        return new AddressBuilder;
    }
}
