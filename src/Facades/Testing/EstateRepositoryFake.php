<?php

namespace Katalam\OnOfficeAdapter\Facades\Testing;

use Katalam\OnOfficeAdapter\Query\Testing\EstateBuilderFake;

class EstateRepositoryFake
{
    use FakeResponses;

    /**
     * Returns a new fake estate builder instance.
     */
    public function query(): EstateBuilderFake
    {
        return new EstateBuilderFake($this->fakeResponses);
    }
}
