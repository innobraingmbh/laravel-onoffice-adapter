<?php

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\EstateBuilder;
use Katalam\OnOfficeAdapter\Query\EstateFileBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class EstateRepository
{
    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {
    }

    /**
     * Returns a new estate builder instance.
     */
    public function query(): EstateBuilder
    {
        return new EstateBuilder($this->onOfficeService);
    }

    /**
     * Returns a new estate file builder instance.
     */
    public function files(int $estateId): EstateFileBuilder
    {
        return new EstateFileBuilder($this->onOfficeService, $estateId);
    }
}
