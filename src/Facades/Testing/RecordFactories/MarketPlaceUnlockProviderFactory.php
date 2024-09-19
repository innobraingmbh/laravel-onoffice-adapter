<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories;

use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\Concerns\SuccessTrait;

class MarketPlaceUnlockProviderFactory extends BaseFactory
{
    use SuccessTrait;

    public function id(int $id): static
    {
        return $this;
    }

    public function type(string $type): static
    {
        return $this;
    }

    public function elements(): static
    {
        return $this;
    }
}
