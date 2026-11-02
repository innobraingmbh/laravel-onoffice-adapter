<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\AddressBuilder;
use Innobrain\OnOfficeAdapter\Query\AddressFileBuilder;
use Innobrain\OnOfficeAdapter\Query\AddressHierarchyContactBuilder;

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

    /**
     * Returns a new address hierarchy contact builder instance.
     */
    public function hierarchyContacts(int $addressId): AddressHierarchyContactBuilder
    {
        /** @var AddressHierarchyContactBuilder */
        return $this->createBuilderFromClass(AddressHierarchyContactBuilder::class, $addressId);
    }
}
