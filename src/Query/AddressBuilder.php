<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Query\Concerns\Input;
use Katalam\OnOfficeAdapter\Query\Concerns\RecordIds;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class AddressBuilder extends Builder
{
    use Input;
    use RecordIds;

    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {}

    public function get(): Collection
    {
        $orderBy = $this->getOrderBy();

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        return $this->onOfficeService->requestAll(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) use ($sortOrder, $sortBy) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Read,
                OnOfficeResourceType::Address,
                parameters: [
                    OnOfficeService::RECORDIDS => $this->recordIds,
                    OnOfficeService::DATA => $this->columns,
                    OnOfficeService::FILTER => $this->getFilters(),
                    OnOfficeService::LISTLIMIT => $pageSize,
                    OnOfficeService::LISTOFFSET => $offset,
                    OnOfficeService::SORTBY => $sortBy,
                    OnOfficeService::SORTORDER => $sortOrder,
                    ...$this->customParameters,
                ]
            );
        }, pageSize: $this->limit, offset: $this->offset, take: $this->take);
    }

    /**
     * @throws OnOfficeException
     */
    public function first(): ?array
    {
        $orderBy = $this->getOrderBy();

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Read,
            OnOfficeResourceType::Address,
            parameters: [
                OnOfficeService::RECORDIDS => $this->recordIds,
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::LISTLIMIT => $this->limit,
                OnOfficeService::LISTOFFSET => $this->offset,
                OnOfficeService::SORTBY => $sortBy,
                OnOfficeService::SORTORDER => $sortOrder,
                ...$this->customParameters,
            ]
        );

        return $response->json('response.results.0.data.records.0');
    }

    /**
     * @throws OnOfficeException
     */
    public function find(int $id): array
    {
        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Read,
            OnOfficeResourceType::Address,
            $id,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                ...$this->customParameters,
            ]
        );

        return $response->json('response.results.0.data.records.0');
    }

    public function each(callable $callback): void
    {
        $orderBy = $this->getOrderBy();

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        $this->onOfficeService->requestAllChunked(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) use ($sortOrder, $sortBy) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Read,
                OnOfficeResourceType::Address,
                parameters: [
                    OnOfficeService::RECORDIDS => $this->recordIds,
                    OnOfficeService::DATA => $this->columns,
                    OnOfficeService::FILTER => $this->getFilters(),
                    OnOfficeService::LISTLIMIT => $pageSize,
                    OnOfficeService::LISTOFFSET => $offset,
                    OnOfficeService::SORTBY => $sortBy,
                    OnOfficeService::SORTORDER => $sortOrder,
                    ...$this->customParameters,
                ]
            );
        }, $callback, pageSize: $this->limit, offset: $this->offset, take: $this->take);
    }

    /**
     * @throws OnOfficeException
     */
    public function modify(int $id): bool
    {
        $this->onOfficeService->requestApi(
            OnOfficeAction::Modify,
            OnOfficeResourceType::Address,
            $id,
            parameters: $this->modifies,
        );

        return true;
    }

    /**
     * @throws OnOfficeException
     */
    public function count(): int
    {
        $orderBy = $this->getOrderBy();

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Read,
            OnOfficeResourceType::Address,
            parameters: [
                OnOfficeService::RECORDIDS => $this->recordIds,
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                OnOfficeService::LISTLIMIT => $this->limit,
                OnOfficeService::LISTOFFSET => $this->offset,
                OnOfficeService::SORTBY => $sortBy,
                OnOfficeService::SORTORDER => $sortOrder,
                ...$this->customParameters,
            ]
        );

        return $response->json('response.results.0.data.meta.cntabsolute', 0);
    }

    public function addCountryIsoCodeType(string $countryIsoCodeType): static
    {
        $this->customParameters['countryIsoCodeType'] = $countryIsoCodeType;

        return $this;
    }

    /**
     * @throws OnOfficeException
     */
    public function create(array $data): array
    {
        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Create,
            OnOfficeResourceType::Address,
            parameters: $data,
        );

        return $response->json('response.results.0.data.records.0');
    }

    public function search(): Collection
    {
        return $this->onOfficeService->requestAll(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::Search,
                OnOfficeResourceId::Address,
                parameters: [
                    OnOfficeService::INPUT => $this->input,
                    OnOfficeService::SORTBY => data_get(array_keys($this->orderBy), 0),
                    OnOfficeService::SORTORDER => data_get($this->orderBy, 0),
                    OnOfficeService::LISTLIMIT => $pageSize,
                    OnOfficeService::LISTOFFSET => $offset,
                    ...$this->customParameters,
                ],
            );
        }, pageSize: $this->limit, offset: $this->offset, take: $this->take);
    }
}
