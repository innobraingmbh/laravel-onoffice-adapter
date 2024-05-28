<?php

namespace Katalam\OnOfficeAdapter\Query\Concerns;

trait NonOrderable
{
    public function orderBy(string $column, string $direction = 'asc'): static
    {
        return $this;
    }

    public function orderByDesc(string $column): static
    {
        return $this;
    }
}
