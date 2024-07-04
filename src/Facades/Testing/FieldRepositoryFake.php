<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades\Testing;

use Katalam\OnOfficeAdapter\Query\Testing\FieldBuilderFake;

class FieldRepositoryFake
{
    use FakeResponses;

    /**
     * Returns a new fake estate builder instance.
     */
    public function query(): FieldBuilderFake
    {
        return new FieldBuilderFake($this->fakeResponses);
    }
}
