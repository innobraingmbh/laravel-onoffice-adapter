<?php

namespace Katalam\OnOfficeAdapter\Query\Concerns;

trait NonSelectable
{
    public function select(array|string $columns = ['ID']): self
    {
        return $this;
    }

    public function addSelect(array|string $column): self
    {
        return $this;
    }
}
