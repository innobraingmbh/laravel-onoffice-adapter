<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades\Testing;

use Katalam\OnOfficeAdapter\Query\Testing\EstateBuilderFake;
use Katalam\OnOfficeAdapter\Query\Testing\EstateFileBuilderFake;

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

    /**
     * Returns a new fake estate files builder instance.
     */
    public function files(): EstateFileBuilderFake
    {
        return new EstateFileBuilderFake($this->fakeResponses);
    }
}
