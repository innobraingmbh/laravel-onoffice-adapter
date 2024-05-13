<?php

namespace Katalam\OnOfficeAdapter\Facades\Testing;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Query\Testing\EstateBuilderFake;

class EstateRepositoryFake
{
    public Collection $fakeResponses;

    public function __construct(array ...$fakeResponses)
    {
        $this->fakeResponses = collect($fakeResponses);
    }

    /**
     * Returns a new fake estate builder instance.
     */
    public function query(): EstateBuilderFake
    {
        return new EstateBuilderFake($this->fakeResponses);
    }
}
