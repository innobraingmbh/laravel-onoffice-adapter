<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Dtos\PaginatedResponse;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\Input;
use Innobrain\OnOfficeAdapter\Query\Concerns\Paginate;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class EstateBuilder extends Builder
{
    use Input;
    use Paginate;

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::SORTBY => $this->getOrderBy(),
                ...$this->customParameters,
            ]
        );

        return $this->requestAll($request);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function first(): ?array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::LISTLIMIT => $this->limit,
                OnOfficeService::LISTOFFSET => $this->offset,
                OnOfficeService::SORTBY => $this->getOrderBy(),
                ...$this->customParameters,
            ]
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function find(int $id): ?array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
            $id,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::SORTBY => $this->getOrderBy(),
                ...$this->customParameters,
            ]
        );

        $this->requestAllChunked($request, $callback);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function modify(int $id): bool
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Modify,
            OnOfficeResourceType::Estate,
            $id,
            parameters: [
                OnOfficeService::DATA => $this->modifies,
                ...$this->customParameters,
            ],
        );

        $this->requestApi($request);

        return true;
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function create(array $data): array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Create,
            OnOfficeResourceType::Estate,
            parameters: [
                OnOfficeService::DATA => $data,
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * @throws OnOfficeException
     */
    public function search(): Collection
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Search,
            OnOfficeResourceId::Estate,
            parameters: [
                OnOfficeService::INPUT => $this->input,
                OnOfficeService::SORTBY => data_get(array_keys($this->orderBy), 0),
                OnOfficeService::SORTORDER => data_get($this->orderBy, 0),
                OnOfficeService::FILTER => $this->getFilters(),
                ...$this->customParameters,
            ],
        );

        return $this->requestAll($request);
    }

    /**
     * Returns the number of records that match the query. This number is from the API
     * and might be lower than the actual number of records when queried with get().
     *
     * @throws OnOfficeException
     */
    public function count(): int
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
            parameters: [
                OnOfficeService::DATA => [],
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::LISTLIMIT => 1,
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.meta.cntabsolute', 0);
    }

    /**
     * Fetch a single page of results.
     *
     * @throws Throwable<OnOfficeException>
     */
    protected function getPage(): Collection
    {
        return $this->getPageWithMeta()->items;
    }

    /**
     * Fetch a single page of results with metadata (total count).
     *
     * @throws Throwable<OnOfficeException>
     */
    protected function getPageWithMeta(): PaginatedResponse
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::SORTBY => $this->getOrderBy(),
                OnOfficeService::LISTLIMIT => $this->pageSize,
                OnOfficeService::LISTOFFSET => $this->offset,
                ...$this->customParameters,
            ]
        );

        $response = $this->requestApi($request);

        return new PaginatedResponse(
            items: collect($response->json('response.results.0.data.records', [])),
            total: $response->json('response.results.0.data.meta.cntabsolute', 0),
        );
    }
}
