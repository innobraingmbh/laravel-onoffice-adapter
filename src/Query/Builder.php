<?php

declare(strict_types=1);

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
     * The take for the number of results.
     */
    public int $take = -1;

    /**
     * An array of columns to order by.
     * Each element should be an array with the column name and the direction.
     */
    public array $orderBy = [];

    /**
     * The offset for the query.
     */
    public int $offset = 0;

    /*
     * An array of custom parameters.
     */
    public array $customParameters = [];

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

    /**
     * Be aware that the limit is capped at 500.
     * Be aware that the limit will change the page size of the result.
     * Not the number of results.
     */
    public function limit(int $value): static
    {
        $this->limit = max(0, $value);

        return $this;
    }

    /**
     * Be aware that the take will change the number of results.
     * Be aware that the take will not change the page size of the result.
     */
    public function take(int $value): static
    {
        $this->take = max(-1, $value);

        return $this;
    }

    public function where(string $column, mixed $operator, mixed $value = null): static
    {
        if (is_null($value)) {
            $value = $operator;
            $operator = '=';
        }

        $this->filters[$column][] = [$operator, $value];

        return $this;
    }

    protected function getFilters(): array
    {
        return collect($this->filters)->mapWithKeys(function (array $value, string $column) {
            return [
                $column => collect($value)->map(function ($filter) {
                    [$operator, $value] = $filter;

                    return [
                        'op' => $operator,
                        'val' => $value,
                    ];
                })->toArray(),
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

    public function parameter(string $key, mixed $value): static
    {
        $this->customParameters[$key] = $value;

        return $this;
    }

    public function parameters(array $parameters): static
    {
        $this->customParameters = array_replace_recursive($this->customParameters, $parameters);

        return $this;
    }

    abstract public function get(): Collection;

    abstract public function first(): ?array;

    abstract public function find(int $id): array;

    abstract public function each(callable $callback): void;

    abstract public function modify(int $id): bool;
}
