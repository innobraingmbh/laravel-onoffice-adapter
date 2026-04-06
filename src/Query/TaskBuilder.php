<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class TaskBuilder extends Builder
{
    public ?int $relatedEstateId = null;

    public ?int $relatedAddressId = null;

    /**
     * @var int|array<int, int>|null
     */
    public int|array|null $relatedProjectIds = null;

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $orderBy = $this->getOrderBy();

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Task,
            parameters: [
                ...$this->prepareRelatedParameters(),
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::SORTBY => data_get(array_keys($orderBy), 0),
                OnOfficeService::SORTORDER => data_get($orderBy, 0),
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
        $orderBy = $this->getOrderBy();

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Task,
            parameters: [
                ...$this->prepareRelatedParameters(),
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::LISTLIMIT => $this->limit > 0 ? $this->limit : $this->pageSize,
                OnOfficeService::LISTOFFSET => $this->offset,
                OnOfficeService::SORTBY => data_get(array_keys($orderBy), 0),
                OnOfficeService::SORTORDER => data_get($orderBy, 0),
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
            OnOfficeResourceType::Task,
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
        $orderBy = $this->getOrderBy();

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Task,
            parameters: [
                ...$this->prepareRelatedParameters(),
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::SORTBY => data_get(array_keys($orderBy), 0),
                OnOfficeService::SORTORDER => data_get($orderBy, 0),
                ...$this->customParameters,
            ],
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
            OnOfficeResourceType::Task,
            parameters: [
                ...$this->prepareRelatedParameters(),
                OnOfficeService::DATA => [],
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::LISTLIMIT => 1,
                ...$this->customParameters,
            ]
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.meta.cntabsolute', 0);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws Throwable<OnOfficeException>
     */
    public function create(array $data): array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Create,
            OnOfficeResourceType::Task,
            parameters: [
                ...$this->prepareRelatedParameters(),
                OnOfficeService::DATA => $data,
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function modify(int $id): bool
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Modify,
            OnOfficeResourceType::Task,
            $id,
            parameters: [
                ...$this->prepareRelatedParameters(),
                OnOfficeService::DATA => $this->modifies,
                ...$this->customParameters,
            ],
        );

        $this->requestApi($request);

        return true;
    }

    public function relatedEstateId(int $estateId): static
    {
        $this->relatedEstateId = $estateId;

        return $this;
    }

    public function relatedAddressId(int $addressId): static
    {
        $this->relatedAddressId = $addressId;

        return $this;
    }

    /**
     * @param  int|array<int, int>  $projectIds
     */
    public function relatedProjectIds(int|array $projectIds): static
    {
        $this->relatedProjectIds = $projectIds;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareRelatedParameters(): array
    {
        $parameters = [];

        if (! is_null($this->relatedEstateId)) {
            $parameters['relatedEstateId'] = $this->relatedEstateId;
        }

        if (! is_null($this->relatedAddressId)) {
            $parameters['relatedAddressId'] = $this->relatedAddressId;
        }

        if (! is_null($this->relatedProjectIds)) {
            $parameters['relatedProjectIds'] = $this->relatedProjectIds;
        }

        return $parameters;
    }
}
