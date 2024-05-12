<?php

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class AddressBuilder extends Builder
{
    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {
    }

    public function get(): Collection
    {
        $columns = $this->columns;
        $filter = $this->getFilters();
        $listLimit = $this->limit;
        $listOffset = $this->offset;
        $orderBy = $this->getOrderBy();

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        return $this->onOfficeService->requestAll(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) use ($sortOrder, $sortBy, $filter, $columns) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Read,
                OnOfficeResourceType::Address,
                parameters: [
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
}