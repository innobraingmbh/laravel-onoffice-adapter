<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\AddressBuilder;
use Innobrain\OnOfficeAdapter\Query\AddressFileBuilder;

class AddressRepository extends BaseRepository
{
    protected function createBuilder(): AddressBuilder
    {
        return new AddressBuilder;
    }

    /**
     * Returns a new address file builder instance.
     */
    public function files(int $addressId): AddressFileBuilder
    {
        /** @var AddressFileBuilder */
        return $this->createBuilderFromClass(AddressFileBuilder::class, $addressId);
    }
}
