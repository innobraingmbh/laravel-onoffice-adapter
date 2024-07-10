<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\SearchCriteriaBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class SearchCriteriaRepository
{
    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {}

    /**
     * Returns a new user builder instance.
     */
    public function query(): SearchCriteriaBuilder
    {
        return new SearchCriteriaBuilder($this->onOfficeService);
    }
}
