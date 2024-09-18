<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\SearchCriteriaBuilder;

class SearchCriteriaRepository extends BaseRepository
{
    protected function createBuilder(): SearchCriteriaBuilder
    {
        return new SearchCriteriaBuilder;
    }
}
