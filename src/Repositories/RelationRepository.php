<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\RelationBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class RelationRepository
{
    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {}

    /**
     * Returns a new relation builder instance.
     */
    public function query(): RelationBuilder
    {
        return new RelationBuilder($this->onOfficeService);
    }
}
