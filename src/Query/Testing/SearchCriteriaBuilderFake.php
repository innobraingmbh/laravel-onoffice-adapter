<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query\Testing;

class SearchCriteriaBuilderFake extends BaseFake
{
    public function __call(string $name, array $arguments): static
    {
        return $this;
    }
}
