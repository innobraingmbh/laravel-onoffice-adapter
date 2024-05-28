<?php

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;

abstract class Builder
{
    use Conditionable;

    /**
     * An array of columns to be selected.
     */
    public array $columns = [];

    /**
     * An array of filters.
     */
    public array $filters = [];

    /**
     * An array of modify parameters.
     */
    public array $modifies = [];

    /**
     * The limit for the query.
     */
    public int $limit = 500;

    /**
     * An array of columns to order by.
     * Each element should be an array with the column name and the direction.
     */
    public array $orderBy = [];

    /**
     * The offset for the query.
     */
    public int $offset = 0;

    public function select(array|string $columns = ['ID']): static
    {
        $this->columns = Arr::wrap($columns);

        return $this;
    }

    public function addSelect(array|string $column): static
    {
        $column = Arr::wrap($column);

        $this->columns = array_merge($this->columns, $column);

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $direction = Str::upper($direction);

        $this->orderBy[] = [$column, $direction];

        return $this;
    }

    public function orderByDesc(string $column): static
    {
        return $this->orderBy($column, 'desc');
    }

    public function addModify(string|array $column, mixed $value = null): static
    {
        if (is_array($column)) {
            $this->modifies = array_merge($this->modifies, $column);

            return $this;
        }

        $this->modifies[$column] = $value;

        return $this;
    }

    public function offset(int $value): static
    {
        $this->offset = max(0, $value);

        return $this;
    }

    public function limit(int $value): static
    {
        $this->limit = max(0, $value);

        return $this;
    }

    public function where(string $column, mixed $operator, mixed $value = null): static
    {
        if (is_null($value)) {
            $value = $operator;
            $operator = '=';
        }

        $this->filters[] = [$column, $operator, $value];

        return $this;
    }

    protected function getFilters(): array
    {
        return collect($this->filters)->mapWithKeys(function ($value) {
            [$column, $operator, $value] = $value;

            return [
                $column => [
                    'op' => $operator,
                    'val' => $value,
                ],
            ];
        })->toArray();
    }

    protected function getOrderBy(): array
    {
        return collect($this->orderBy)->mapWithKeys(function ($value) {
            [$column, $direction] = $value;

            return [
                $column => $direction,
            ];
        })->toArray();
    }

    abstract public function get(): Collection;

    abstract public function first(): array;

    abstract public function find(int $id): array;

    abstract public function each(callable $callback): void;

    abstract public function modify(int $id): bool;
}
