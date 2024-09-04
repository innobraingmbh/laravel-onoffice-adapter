<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query\Testing;

use Exception;
use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\BaseFactory;
use Katalam\OnOfficeAdapter\Query\Builder;
use Throwable;

class BaseFake extends Builder
{
    public function __construct(
        public Collection $fakeResponses
    ) {}

    /**
     * @throws Throwable
     */
    public function get(): Collection
    {
        throw_if($this->fakeResponses->isEmpty(), new Exception('No more fake responses'));

        $nextRequest = $this->fakeResponses->shift();

        return collect($nextRequest)
            ->flatten()
            ->map(function (BaseFactory|OnOfficeException|null $factory) {
                if ($factory instanceof OnOfficeException) {
                    throw $factory;
                }

                return $factory?->toArray();
            });
    }

    /**
     * @throws Throwable
     */
    public function first(): ?array
    {
        return $this->get()->first();
    }

    /**
     * @throws Throwable
     */
    public function find(int $id): array
    {
        return $this->get()
            ->first(fn (array $record) => $record['id'] === $id) ?? [];
    }

    /**
     * @throws Throwable
     */
    public function each(callable $callback): void
    {
        throw_if($this->fakeResponses->isEmpty(), new Exception('No more fake responses'));

        $nextRequest = $this->fakeResponses->shift();

        collect($nextRequest)
            ->each(function (array $factories) use ($callback) {
                $records = collect($factories)
                    ->map(function (BaseFactory|OnOfficeException|null $factory) {
                        if ($factory instanceof OnOfficeException) {
                            throw $factory;
                        }

                        return $factory?->toArray();
                    })
                    ->toArray();

                $callback($records);
            });
    }

    /**
     * @throws Throwable
     */
    public function modify(int $id): bool
    {
        throw_if($this->fakeResponses->isEmpty(), new Exception('No more fake responses'));

        $nextRequest = $this->fakeResponses->shift();

        $first = collect($nextRequest)
            ->flatten()
            ->first();

        if ($first instanceof OnOfficeException) {
            throw $first;
        }

        return $first;
    }
}
