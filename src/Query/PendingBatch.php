<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeApiCredentials;
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
     * The credentials the batch will be sent with, taken from the
     * builders that were added. A batch is one API call, so all
     * requests share them.
     */
    protected ?OnOfficeApiCredentials $credentials = null;

    /**
     * Whether the batch must not hit the live API because a builder
     * came from a faking or stray-preventing repository.
     */
    protected bool $preventStrayRequests = false;

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
     *
     * A builder's credentials apply to the whole batch, since all
     * requests are sent in one API call. Builders with different
     * credentials cannot be batched together.
     *
     * @throws Throwable
     */
    public function add(OnOfficeRequest|Builder ...$requests): static
    {
        foreach ($requests as $request) {
            if ($request instanceof Builder) {
                $this->useCredentials($request->getCredentials());
                $this->preventStrayRequests = $this->preventStrayRequests || $request->preventsStrayRequests();

                $request = $request->toRequest();
            }

            $this->requests[] = $request;
        }

        return $this;
    }

    /**
     * @throws Throwable
     */
    protected function useCredentials(?OnOfficeApiCredentials $credentials): void
    {
        if (is_null($credentials)) {
            return;
        }

        throw_if(
            $this->credentials && ! $this->credentials->equals($credentials),
            OnOfficeException::class,
            'All requests in a batch are sent in one API call and cannot use different credentials.',
        );

        $this->credentials = $credentials;
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
        return $this->repository->dispatch($this->requests, $this->credentials, $this->preventStrayRequests);
    }
}
