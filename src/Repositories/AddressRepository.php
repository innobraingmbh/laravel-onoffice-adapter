<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\AddressBuilder;

class AddressRepository extends BaseRepository
{
    protected function createBuilder(): AddressBuilder
    {
        return new AddressBuilder;
    }
}
