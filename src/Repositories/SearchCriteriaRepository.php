<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\SearchCriteriaBuilder;

class SearchCriteriaRepository extends BaseRepository
{
    protected function createBuilder(): SearchCriteriaBuilder
    {
        return new SearchCriteriaBuilder;
    }
}
