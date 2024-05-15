<?php

namespace Katalam\OnOfficeAdapter\Query\Concerns;

trait NonOrderable
{
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        return $this;
    }

    public function orderByDesc(string $column): self
    {
        return $this;
    }
}
