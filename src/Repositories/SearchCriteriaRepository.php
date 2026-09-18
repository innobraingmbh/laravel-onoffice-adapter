<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\SearchCriteriaBuilder;
use Innobrain\OnOfficeAdapter\Query\SearchCriteriaFieldBuilder;

class SearchCriteriaRepository extends BaseRepository
{
    /**
     * Returns a new search criteria fields builder instance.
     */
    public function fields(): SearchCriteriaFieldBuilder
    {
        /** @var SearchCriteriaFieldBuilder */
        return $this->createBuilderFromClass(SearchCriteriaFieldBuilder::class);
    }

    protected function createBuilder(): SearchCriteriaBuilder
    {
        return new SearchCriteriaBuilder;
    }
}
