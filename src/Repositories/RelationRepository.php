<?php

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\FieldBuilder;
use Katalam\OnOfficeAdapter\Query\RelationBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

readonly class RelationRepository
{
    public function __construct(
        private OnOfficeService $onOfficeService,
    ) {
    }

    /**
     * Returns a new relation builder instance.
     */
    public function query(): RelationBuilder
    {
        return new RelationBuilder($this->onOfficeService);
    }
}
