<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Throwable;

/**
 * The pending batch returned by Bus::batch(). Collects requests and sends
 * them as a single API call via once(). It wraps a BatchBuilder so the public
 * batch surface stays small (add/once) without inheriting the query builder API.
 */
class PendingBatch
{
    public function __construct(
        protected BatchBuilder $builder
    ) {}

    /**
     * Add one or more requests to the batch. A builder can be
     * passed directly and will be converted to its read request.
     *
     * Each request becomes one batch action and returns a single,
     * non-paginated page: the first page only, capped at 500 records.
     */
    public function add(OnOfficeRequest|Builder ...$requests): static
    {
        $this->builder->add(...$requests);

        return $this;
    }

    /**
     * Send all added requests in a single API call.
     * Returns one result element per request, in the same order.
     *
     * Each result is a single, non-paginated page: the first page only,
     * capped at 500 records per action.
     *
     * @return Collection<int, array<string, mixed>>
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function once(): Collection
    {
        return $this->builder->dispatch();
    }
}
