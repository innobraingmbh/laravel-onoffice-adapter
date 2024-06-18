<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades\Testing;

use Katalam\OnOfficeAdapter\Query\Testing\AddressBuilderFake;

class AddressRepositoryFake
{
    use FakeResponses;

    /**
     * Returns a new fake estate builder instance.
     */
    public function query(): AddressBuilderFake
    {
        return new AddressBuilderFake($this->fakeResponses);
    }
}
