<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades\Testing;

use Katalam\OnOfficeAdapter\Query\Testing\SearchCriteriaBuilderFake;

class SearchCriteriaRepositoryFake
{
    use FakeResponses;

    /**
     * Returns a new fake user builder instance.
     */
    public function query(): SearchCriteriaBuilderFake
    {
        return new SearchCriteriaBuilderFake($this->fakeResponses);
    }
}
