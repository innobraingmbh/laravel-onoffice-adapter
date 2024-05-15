<?php

namespace Katalam\OnOfficeAdapter\Query\Concerns;

trait NonLimitable
{
    public function offset(int $value): self
    {
        return $this;
    }

    public function limit(int $value): self
    {
        return $this;
    }
}
