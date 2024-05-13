<?php

namespace Katalam\OnOfficeAdapter\Facades\Testing;

use Katalam\OnOfficeAdapter\Query\Testing\MarketplaceBuilderFake;

class MarketplaceRepositoryFake
{
    use FakeResponses;

    /**
     * Returns a new fake marketplace builder instance.
     */
    public function query(): MarketplaceBuilderFake
    {
        return new MarketplaceBuilderFake($this->fakeResponses);
    }
}
