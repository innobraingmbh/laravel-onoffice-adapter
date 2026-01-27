<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

use Illuminate\Support\Collection;

interface BuilderInterface
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function get(): Collection;

    /**
     * @return array<string, mixed>|null
     */
    public function first(): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array;

    public function each(callable $callback): void;

    public function modify(int $id): bool;
}
