<?php

namespace Katalam\OnOfficeAdapter\Query\Concerns;

trait NonLimitable
{
    public function offset(int $value): static
    {
        return $this;
    }

    public function limit(int $value): static
    {
        return $this;
    }
}
