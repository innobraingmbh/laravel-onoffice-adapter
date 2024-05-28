<?php

namespace Katalam\OnOfficeAdapter\Query\Concerns;

trait NonSelectable
{
    public function select(array|string $columns = ['ID']): static
    {
        return $this;
    }

    public function addSelect(array|string $column): static
    {
        return $this;
    }
}
