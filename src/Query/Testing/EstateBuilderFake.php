<?php

namespace Katalam\OnOfficeAdapter\Query\Testing;

use Exception;
use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\BaseFactory;
use Katalam\OnOfficeAdapter\Query\Builder;
use Throwable;

class EstateBuilderFake extends Builder
{
    public function __construct(
        public Collection $fakeResponses
    ) {
    }

    /**
     * @throws Throwable
     */
    public function get(): Collection
    {
        $nextRequest = $this->fakeResponses->shift();
        throw_if($nextRequest === null, new Exception('No more fake responses'));

        return collect($nextRequest)
            ->flatten()
            ->map(fn (BaseFactory $factory) => $factory->toArray());
    }

    /**
     * @throws Throwable
     */
    public function first(): array
    {
        return $this->get()->first();
    }

    /**
     * @throws Throwable
     */
    public function find(int $id): array
    {
        return $this->get()
            ->first(fn (array $record) => $record['id'] === $id);
    }

    /**
     * @throws Throwable
     */
    public function each(callable $callback): void
    {
        $nextRequest = $this->fakeResponses->shift();
        throw_if($nextRequest === null, new Exception('No more fake responses'));

        collect($nextRequest)
            ->each(function (array $factories) use ($callback) {
                $records = collect($factories)
                    ->map(fn (BaseFactory $factory) => $factory->toArray())
                    ->toArray();

                $callback($records);
            });
    }
}
