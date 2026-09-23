<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\SearchCriteriaBuilder;
use Innobrain\OnOfficeAdapter\Query\SearchCriteriaFieldBuilder;
use Innobrain\OnOfficeAdapter\Query\SearchCriteriaMatchBuilder;

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

    /**
     * Returns a new builder for the search criteria that match a set of field values.
     *
     * @param  array<string, mixed>  $values
     */
    public function matching(array $values = []): SearchCriteriaMatchBuilder
    {
        /** @var SearchCriteriaMatchBuilder $builder */
        $builder = $this->createBuilderFromClass(SearchCriteriaMatchBuilder::class);

        return $builder->searchData($values);
    }

    protected function createBuilder(): SearchCriteriaBuilder
    {
        return new SearchCriteriaBuilder;
    }
}
