<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorImpl;
use Illuminate\Pagination\Paginator as PaginatorImpl;
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\PaginatedResponse;

trait Paginate
{
    /**
     * Paginate the results.
     *
     * Returns a LengthAwarePaginator with total count.
     * This requires 1 API call that returns both records and total count.
     *
     * @param  int|null  $perPage  Number of items per page (max 500)
     * @param  string  $pageName  Query string parameter name for page number
     * @param  int|null  $page  Current page number (reads from request if null)
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
     * @param  int|null  $perPage  Number of items per page (max 500)
     * @param  string  $pageName  Query string parameter name for page number
     * @param  int|null  $page  Current page number (reads from request if null)
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
     * Must be implemented by each builder.
     */
    abstract protected function getPage(): Collection;

    /**
     * Fetch a single page of results with metadata (total count).
     * Must be implemented by each builder.
     */
    abstract protected function getPageWithMeta(): PaginatedResponse;

    /**
     * Get the total count of records matching the query.
     * Must be implemented by each builder.
     */
    abstract public function count(): int;

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
