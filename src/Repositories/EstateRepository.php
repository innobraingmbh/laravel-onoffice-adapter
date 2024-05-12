<?php

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\EstateBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

readonly class EstateRepository
{
    public function __construct(
        private OnOfficeService $onOfficeService,
    ) {
    }

    /**
     * Returns a new estate builder instance.
     */
    public function query(): EstateBuilder
    {
        return new EstateBuilder($this->onOfficeService);
    }
}
