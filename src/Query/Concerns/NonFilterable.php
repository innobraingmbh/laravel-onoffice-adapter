<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

trait NonFilterable
{
    public function where(string $column, mixed $operator, mixed $value = null): static
    {
        return $this;
    }

    public function whereNot(string $column, mixed $value): static
    {
        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        return $this;
    }

    public function whereNotIn(string $column, array $values): static
    {
        return $this;
    }

    public function whereBetween(string $column, int|string $start, int|string $end): static
    {
        return $this;
    }

    public function whereLike(string $column, string $value): static
    {
        return $this;
    }

    public function whereNotLike(string $column, string $value): static
    {
        return $this;
    }
}
