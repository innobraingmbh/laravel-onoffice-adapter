<?php

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\FieldBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

readonly class FieldRepository
{
    public function __construct(
        private OnOfficeService $onOfficeService,
    ) {
    }

    /**
     * Returns a new field builder instance.
     */
    public function query(): FieldBuilder
    {
        return new FieldBuilder($this->onOfficeService);
    }
}
