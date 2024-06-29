<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\ActivityBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class ActivityRepository
{
    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {}

    /**
     * Returns a new address builder instance.
     */
    public function query(): ActivityBuilder
    {
        return new ActivityBuilder($this->onOfficeService);
    }
}
