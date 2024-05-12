<?php

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\AddressBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

readonly class AddressRepository
{
    public function __construct(
        private OnOfficeService $onOfficeService,
    ) {
    }

    /**
     * Returns a new address builder instance.
     */
    public function query(): AddressBuilder
    {
        return new AddressBuilder($this->onOfficeService);
    }
}
