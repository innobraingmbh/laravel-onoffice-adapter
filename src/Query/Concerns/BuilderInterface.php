<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

use Illuminate\Support\Collection;

interface BuilderInterface
{
    public function get(): Collection;

    public function first(): ?array;

    public function find(int $id): array;

    public function each(callable $callback): void;

    public function modify(int $id): bool;
}
