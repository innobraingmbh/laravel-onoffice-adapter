<?php

namespace Katalam\OnOfficeAdapter\Facades\Testing;

use Katalam\OnOfficeAdapter\Query\Testing\UserBuilderFake;

class UserRepositoryFake
{
    use FakeResponses;

    /**
     * Returns a new fake estate builder instance.
     */
    public function query(): UserBuilderFake
    {
        return new UserBuilderFake($this->fakeResponses);
    }
}
