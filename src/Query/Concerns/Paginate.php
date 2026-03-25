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
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

trait Paginate
{
    /**
     * Build the base read request for this builder.
     * Each builder implements this once; all terminal methods call it.
     */
    abstract protected function buildReadRequest(): OnOfficeRequest;

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        return $this->requestAll($this->buildReadRequest());
    }

    /**
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function first(): ?array
    {
        $request = $this->buildReadRequest();
        data_set($request->parameters, OnOfficeService::LISTLIMIT, $this->limit > 0 ? $this->limit : $this->pageSize);
        data_set($request->parameters, OnOfficeService::LISTOFFSET, $this->offset);

        return $this->requestApi($request)->json('response.results.0.data.records.0');
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        $this->requestAllChunked($this->buildReadRequest(), $callback);
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
        $request = $this->buildReadRequest();
        data_set($request->parameters, OnOfficeService::DATA, []);
        data_set($request->parameters, OnOfficeService::LISTLIMIT, 1);

        return $this->requestApi($request)->json('response.results.0.data.meta.cntabsolute', 0);
    }

    /**
     * Paginate the results.
     *
     * Returns a LengthAwarePaginator with total count.
     * This requires 1 API call that returns both records and total count.
     *
     * @param int|null $perPage Number of items per page (max 500)
     * @param string $pageName Query string parameter name for page number
     * @param int|null $page Current page number (reads from request if null)
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function paginate(?int $perPage = 15, string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $perPage = $this->normalizePerPage($perPage);
        $page = $page ?? $this->resolveCurrentPage($pageName);

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
     * @param int|null $perPage Number of items per page (max 500)
     * @param string $pageName Query string parameter name for page number
     * @param int|null $page Current page number (reads from request if null)
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function simplePaginate(?int $perPage = 15, string $pageName = 'page', ?int $page = null): Paginator
    {
        $perPage = $this->normalizePerPage($perPage);
        $page = $page ?? $this->resolveCurrentPage($pageName);

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
        $request = $this->buildReadRequest();
        data_set($request->parameters, OnOfficeService::LISTLIMIT, $this->pageSize);
        data_set($request->parameters, OnOfficeService::LISTOFFSET, $this->offset);

        $response = $this->requestApi($request);

        return new PaginatedResponse(
            items: collect($response->json('response.results.0.data.records', [])),
            total: $response->json('response.results.0.data.meta.cntabsolute', 0),
        );
    }

    /**
     * Normalize the per-page value, capping at API maximum of 500.
     */
    private function normalizePerPage(?int $perPage): int
    {
        $perPage = $perPage ?? 15;
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
