<?php

namespace Katalam\OnOfficeAdapter\Facades\Testing;

use Katalam\OnOfficeAdapter\Query\Testing\ActivityBuilderFake;

class ActivityRepositoryFake
{
    use FakeResponses;

    /**
     * Returns a new fake estate builder instance.
     */
    public function query(): ActivityBuilderFake
    {
        return new ActivityBuilderFake($this->fakeResponses);
    }
}
