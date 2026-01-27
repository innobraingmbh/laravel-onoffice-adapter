<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\Paginate;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class UserBuilder extends Builder
{
    use Paginate;
    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::User,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::SORTBY => $this->getOrderBy(),
                ...$this->customParameters,
            ],
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
            OnOfficeResourceType::User,
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
            OnOfficeResourceType::User,
            $id,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                ...$this->customParameters,
            ]
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
            OnOfficeResourceType::User,
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
     * Returns the number of records that match the query. This number is from the API
     * and might be lower than the actual number of records when queried with get().
     *
     * @throws OnOfficeException
     */
    public function count(): int
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::User,
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
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::User,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::SORTBY => $this->getOrderBy(),
                OnOfficeService::LISTLIMIT => $this->pageSize,
                OnOfficeService::LISTOFFSET => $this->offset,
                ...$this->customParameters,
            ],
        );

        return collect($this->requestApi($request)->json('response.results.0.data.records', []));
    }
}
