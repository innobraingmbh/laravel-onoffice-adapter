<?php

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class AddressBuilder extends Builder
{
    public array $recordIds = [];

    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {
    }

    public function get(): Collection
    {
        $recordIds = $this->recordIds;
        $columns = $this->columns;
        $filter = $this->getFilters();
        $listLimit = $this->limit;
        $listOffset = $this->offset;
        $orderBy = $this->getOrderBy();

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        return $this->onOfficeService->requestAll(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) use ($recordIds, $sortOrder, $sortBy, $filter, $columns) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Read,
                OnOfficeResourceType::Address,
                parameters: [
                    OnOfficeService::RECORDIDS => $recordIds,
                    OnOfficeService::DATA => $columns,
                    OnOfficeService::FILTER => $filter,
                    OnOfficeService::LISTLIMIT => $pageSize,
                    OnOfficeService::LISTOFFSET => $offset,
                    OnOfficeService::SORTBY => $sortBy,
                    OnOfficeService::SORTORDER => $sortOrder,
                ]
            );
        }, pageSize: $listLimit, offset: $listOffset);
    }

    /**
     * @throws OnOfficeException
     */
    public function first(): array
    {
        $recordIds = $this->recordIds;
        $columns = $this->columns;
        $filter = $this->getFilters();
        $listLimit = $this->limit;
        $listOffset = $this->offset;
        $orderBy = $this->getOrderBy();

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Read,
            OnOfficeResourceType::Address,
            parameters: [
                OnOfficeService::RECORDIDS => $recordIds,
                OnOfficeService::DATA => $columns,
                OnOfficeService::FILTER => $filter,
                OnOfficeService::LISTLIMIT => $listLimit,
                OnOfficeService::LISTOFFSET => $listOffset,
                OnOfficeService::SORTBY => $sortBy,
                OnOfficeService::SORTORDER => $sortOrder,
            ]
        );

        return $response->json('response.results.0.data.records.0');
    }

    /**
     * @throws OnOfficeException
     */
    public function find(int $id): array
    {
        $columns = $this->columns;

        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Get,
            OnOfficeResourceType::Address,
            $id,
            parameters: [
                OnOfficeService::DATA => $columns,
            ]
        );

        return $response->json('response.results.0.data.records.0');
    }

    public function each(callable $callback): void
    {
        $recordIds = $this->recordIds;
        $columns = $this->columns;
        $filter = $this->getFilters();
        $listLimit = $this->limit;
        $listOffset = $this->offset;
        $orderBy = $this->getOrderBy();

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        $this->onOfficeService->requestAllChunked(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) use ($recordIds, $sortOrder, $sortBy, $filter, $columns) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Read,
                OnOfficeResourceType::Address,
                parameters: [
                    OnOfficeService::RECORDIDS => $recordIds,
                    OnOfficeService::DATA => $columns,
                    OnOfficeService::FILTER => $filter,
                    OnOfficeService::LISTLIMIT => $pageSize,
                    OnOfficeService::LISTOFFSET => $offset,
                    OnOfficeService::SORTBY => $sortBy,
                    OnOfficeService::SORTORDER => $sortOrder,
                ]
            );
        }, $callback, pageSize: $listLimit, offset: $listOffset);
    }

    public function recordIds(array|int $recordIds): self
    {
        $this->recordIds = Arr::wrap($recordIds);

        return $this;
    }

    public function addRecordIds(int|array $recordId): self
    {
        $this->recordIds = array_merge($this->recordIds, Arr::wrap($recordId));

        return $this;
    }
}
