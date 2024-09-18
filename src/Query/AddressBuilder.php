<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Query\Concerns\Input;
use Katalam\OnOfficeAdapter\Query\Concerns\RecordIds;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class AddressBuilder extends Builder
{
    use Input;
    use RecordIds;

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $orderBy = $this->getOrderBy();

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Address,
            parameters: [
                OnOfficeService::RECORDIDS => $this->recordIds,
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::SORTBY => $sortBy,
                OnOfficeService::SORTORDER => $sortOrder,
                ...$this->customParameters,
            ],
        );

        return $this->requestAll($request);
    }

    /**
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function first(): ?array
    {
        $orderBy = $this->getOrderBy();

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Address,
            parameters: [
                OnOfficeService::RECORDIDS => $this->recordIds,
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
    public function find(int $id): array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Address,
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

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Address,
            parameters: [
                OnOfficeService::RECORDIDS => $this->recordIds,
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::SORTBY => $sortBy,
                OnOfficeService::SORTORDER => $sortOrder,
                ...$this->customParameters,
            ],
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
            OnOfficeResourceType::Address,
            $id,
            parameters: $this->modifies,
        );

        $this->requestApi($request);

        return true;
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function count(): int
    {
        $orderBy = $this->getOrderBy();

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Address,
            parameters: [
                OnOfficeService::RECORDIDS => $this->recordIds,
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::LISTLIMIT => $this->limit > 0 ? $this->limit : $this->pageSize,
                OnOfficeService::SORTBY => data_get(array_keys($orderBy), 0),
                OnOfficeService::SORTORDER => data_get($orderBy, 0),
                ...$this->customParameters,
            ]
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.meta.cntabsolute', 0);
    }

    public function addCountryIsoCodeType(string $countryIsoCodeType): static
    {
        $this->customParameters['countryIsoCodeType'] = $countryIsoCodeType;

        return $this;
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function create(array $data): array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Create,
            OnOfficeResourceType::Address,
            parameters: $data,
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
            OnOfficeResourceId::Address,
            parameters: [
                OnOfficeService::INPUT => $this->input,
                OnOfficeService::SORTBY => data_get(array_keys($this->orderBy), 0),
                OnOfficeService::SORTORDER => data_get($this->orderBy, 0),
                ...$this->customParameters,
            ],
        );

        return $this->requestAll($request);
    }
}
