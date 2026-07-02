<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorImpl;
use Illuminate\Pagination\Paginator as PaginatorImpl;
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Dtos\PaginatedResponse;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

trait Paginate
{
    /**
     * The id of a single record to read, set via withId(). When present, reads
     * target that one record instead of a list, which is what lets one record be
     * read inside a batch via Query::batch().
     */
    protected int|string|null $readResourceId = null;

    /**
     * Build the list read request for this builder.
     */
    abstract protected function buildReadRequest(): OnOfficeRequest;

    /**
     * Build the request to read a single record by its id. This is the lazy twin
     * of find(): both go through it, so a record read inside a batch is identical
     * to one read eagerly.
     */
    abstract protected function buildFindRequest(int|string $id): OnOfficeRequest;

    /**
     * Target a single record by its id. The read then returns just that record,
     * which is how one record is read inside a batch via Query::batch(). On its
     * own, prefer find() — it returns the record itself.
     */
    public function withId(int|string $id): static
    {
        $this->readResourceId = $id;

        return $this;
    }

    /**
     * Read a single record by its id, or null when it is missing.
     *
     * @return array<string, mixed>|null
     *
     * @throws OnOfficeException
     */
    public function find(int|string $id): ?array
    {
        return $this->requestApi($this->buildFindRequest($id))
            ->json(OnOfficeResponsePath::FIRST_RECORD);
    }

    /**
     * An id-scoped read targets exactly one record, so the list window does
     * not apply. Skipping it keeps a withId() read identical to find() on
     * the wire.
     *
     * @throws OnOfficeException
     */
    protected function applyListWindow(OnOfficeRequest $request, int $listLimit, int $offset): void
    {
        if ($this->readResourceId !== null) {
            return;
        }

        parent::applyListWindow($request, $listLimit, $offset);
    }

    /**
     * The entry point every terminal read goes through. Reads a single record
     * when one was targeted via withId(), otherwise the list.
     */
    protected function readRequest(): OnOfficeRequest
    {
        return $this->readResourceId === null
            ? $this->buildReadRequest()
            : $this->buildFindRequest($this->readResourceId);
    }

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        return $this->requestAll($this->readRequest());
    }

    /**
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function first(): ?array
    {
        $request = $this->readRequest();
        $this->applyListWindow($request, $this->limit > 0 ? $this->limit : $this->pageSize, $this->offset);

        return $this->requestApi($request)->json(OnOfficeResponsePath::FIRST_RECORD);
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        $this->requestAllChunked($this->readRequest(), $callback);
    }

    /**
     * Build the read request this builder would send, without sending it.
     * Useful for sending multiple requests in one batch. Since a batched
     * request is never paginated, the limit and offset are baked into the
     * request, and only the first page (max 500 records) is returned. For the
     * full, paginated result set use get() on the repository instead.
     */
    public function toRequest(): OnOfficeRequest
    {
        $request = $this->readRequest();
        $this->applyListWindow($request, $this->limit > 0 ? $this->limit : $this->pageSize, $this->offset);

        return $request;
    }

    /**
     * Returns the number of records that match the query. This number is from the API
     * and might be lower than the actual number of records when queried with get().
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function count(): int
    {
        $request = $this->readRequest();

        if ($this->readResourceId === null) {
            data_set($request->parameters, OnOfficeService::DATA, []);
            data_set($request->parameters, OnOfficeService::LISTLIMIT, $this->countListLimit());
        }

        return $this->requestApi($request)->json(OnOfficeResponsePath::META_COUNT_ABSOLUTE, 0);
    }

    /**
     * The listlimit a count request is sent with. A count wants no records,
     * so the smallest page suffices; an endpoint whose cntabsolute misbehaves
     * at listlimit=1 (the task endpoint) overrides this.
     */
    protected function countListLimit(): int
    {
        return 1;
    }

    /**
     * Paginate the results.
     *
     * Returns a LengthAwarePaginator with total count.
     * This requires 1 API call that returns both records and total count.
     *
     * @param  int|null  $perPage  Number of items per page (max 500)
     * @param  string  $pageName  Query string parameter name for page number
     * @param  int|null  $page  Current page number (reads from request if null)
     * @return LengthAwarePaginator<int, array<string, mixed>>
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function paginate(?int $perPage = 15, string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $perPage = $this->normalizePerPage($perPage);
        $page ??= $this->resolveCurrentPage($pageName);

        $response = $this->forPage($page, $perPage)->getPageWithMeta();

        return new LengthAwarePaginatorImpl(
            $response->items,
            $response->total,
            $perPage,
            $page,
            [
                'path' => PaginatorImpl::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        );
    }

    /**
     * Simple paginate the results.
     *
     * Returns a Paginator without total count (only hasMorePages).
     * This requires 1 API call, fetching perPage+1 items to detect more pages.
     *
     * @param  int|null  $perPage  Number of items per page (max 500)
     * @param  string  $pageName  Query string parameter name for page number
     * @param  int|null  $page  Current page number (reads from request if null)
     * @return Paginator<int, array<string, mixed>>
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function simplePaginate(?int $perPage = 15, string $pageName = 'page', ?int $page = null): Paginator
    {
        $perPage = $this->normalizePerPage($perPage);
        $page ??= $this->resolveCurrentPage($pageName);

        // Fetch one extra record to determine if there are more pages
        // The Paginator constructor will check if count > perPage to set hasMore,
        // then slice to perPage items
        $results = $this->forPage($page, $perPage + 1)->getPage();

        return new PaginatorImpl(
            $results,
            $perPage,
            $page,
            [
                'path' => PaginatorImpl::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        );
    }

    /**
     * Set the query to retrieve a specific page of results.
     *
     * @param  int  $page  Page number (1-indexed)
     * @param  int  $perPage  Number of items per page
     */
    public function forPage(int $page, int $perPage): static
    {
        $perPage = $this->normalizePerPage($perPage);
        $offset = max(0, ($page - 1) * $perPage);

        $this->offset = $offset;
        $this->pageSize = $perPage;
        $this->limit = $perPage;

        return $this;
    }

    /**
     * Fetch a single page of results.
     *
     * @return Collection<int, array<string, mixed>>
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    protected function getPage(): Collection
    {
        return $this->getPageWithMeta()->items;
    }

    /**
     * Fetch a single page of results with metadata (total count).
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    protected function getPageWithMeta(): PaginatedResponse
    {
        $request = $this->readRequest();
        $this->applyListWindow($request, $this->pageSize, $this->offset);

        $response = $this->requestApi($request);

        /** @var array<int, array<string, mixed>> $records */
        $records = $response->json(OnOfficeResponsePath::RECORDS, []);

        return new PaginatedResponse(
            items: collect($records),
            total: $response->json(OnOfficeResponsePath::META_COUNT_ABSOLUTE, 0),
        );
    }

    /**
     * Normalize the per-page value, capping at API maximum of 500.
     */
    private function normalizePerPage(?int $perPage): int
    {
        $perPage ??= 15;
        $perPage = max(1, $perPage);

        return min(500, $perPage);
    }

    /**
     * Resolve the current page from the request.
     */
    private function resolveCurrentPage(string $pageName): int
    {
        $page = request()->input($pageName, 1);

        return max(1, (int) $page);
    }
}
