<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Repositories\BatchRepository;
use Throwable;

/**
 * The pending batch returned by Query::batch(). Collects requests and sends
 * them as a single API call via once(). The repository does the sending, so
 * the public batch surface stays small (add/once).
 */
class PendingBatch
{
    /**
     * The requests that will be sent as one batch.
     *
     * @var array<int, OnOfficeRequest>
     */
    protected array $requests = [];

    /**
     * @param  array<int, OnOfficeRequest|Builder>  $requests
     */
    public function __construct(
        protected BatchRepository $repository,
        array $requests = [],
    ) {
        $this->add(...$requests);
    }

    /**
     * Add one or more requests to the batch. A builder can be
     * passed directly and will be converted to its read request.
     *
     * Each request becomes one batch action and returns a single,
     * non-paginated page: the first page only, capped at 500 records.
     */
    public function add(OnOfficeRequest|Builder ...$requests): static
    {
        foreach ($requests as $request) {
            $this->requests[] = $request instanceof Builder ? $request->toRequest() : $request;
        }

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
        return $this->repository->dispatch($this->requests);
    }
}
