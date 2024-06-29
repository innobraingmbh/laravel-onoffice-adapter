<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\FieldBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class FieldRepository
{
    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {}

    /**
     * Returns a new field builder instance.
     */
    public function query(): FieldBuilder
    {
        return new FieldBuilder($this->onOfficeService);
    }
}
