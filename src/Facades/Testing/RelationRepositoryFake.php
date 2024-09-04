<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades\Testing;

use Katalam\OnOfficeAdapter\Query\Testing\RelationBuilderFake;

class RelationRepositoryFake
{
    use FakeResponses;

    /**
     * Returns a new fake marketplace builder instance.
     */
    public function query(): RelationBuilderFake
    {
        return new RelationBuilderFake($this->fakeResponses);
    }
}
