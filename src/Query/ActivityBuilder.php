<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Query\Concerns\RecordIds;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class ActivityBuilder extends Builder
{
    use RecordIds;

    public string $estateOrAddress = 'estate';

    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {}

    public function get(): Collection
    {
        $filter = $this->getFilters();
        $orderBy = $this->getOrderBy();

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        return $this->onOfficeService->requestAll(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) use ($sortOrder, $sortBy, $filter) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Read,
                OnOfficeResourceType::Activity,
                parameters: [
                    $this->estateOrAddress => $this->recordIds,
                    OnOfficeService::DATA => $this->columns,
                    OnOfficeService::FILTER => $filter,
                    OnOfficeService::LISTLIMIT => $pageSize,
                    OnOfficeService::LISTOFFSET => $offset,
                    OnOfficeService::SORTBY => $sortBy,
                    OnOfficeService::SORTORDER => $sortOrder,
                    ...$this->customParameters,
                ]
            );
        }, pageSize: $this->limit, offset: $this->offset);
    }

    /**
     * @throws OnOfficeException
     */
    public function first(): array
    {
        $filter = $this->getFilters();
        $orderBy = $this->getOrderBy();

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Read,
            OnOfficeResourceType::Activity,
            parameters: [
                $this->estateOrAddress => $this->recordIds,
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $filter,
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
            OnOfficeAction::Get,
            OnOfficeResourceType::Activity,
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
        $filter = $this->getFilters();
        $orderBy = $this->getOrderBy();

        $sortBy = data_get(array_keys($orderBy), 0);
        $sortOrder = data_get($orderBy, 0);

        $this->onOfficeService->requestAllChunked(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) use ($sortOrder, $sortBy, $filter) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Read,
                OnOfficeResourceType::Activity,
                parameters: [
                    $this->estateOrAddress => $this->recordIds,
                    OnOfficeService::DATA => $this->columns,
                    OnOfficeService::FILTER => $filter,
                    OnOfficeService::LISTLIMIT => $pageSize,
                    OnOfficeService::LISTOFFSET => $offset,
                    OnOfficeService::SORTBY => $sortBy,
                    OnOfficeService::SORTORDER => $sortOrder,
                    ...$this->customParameters,
                ]
            );
        }, $callback, pageSize: $this->limit, offset: $this->offset);
    }

    /**
     * @throws OnOfficeException
     */
    public function modify(int $id): bool
    {
        throw new OnOfficeException('Not implemented yet');
    }

    /**
     * @throws OnOfficeException
     */
    public function create(array $data): array
    {
        $data = array_replace($data, [
            $this->estateOrAddress => $this->recordIds,
        ]);

        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Create,
            OnOfficeResourceType::Activity,
            parameters: $data,
        );

        return $response->json('response.results.0.data.records.0');
    }

    public function estate(): static
    {
        $this->estateOrAddress = 'estateid';

        return $this;
    }

    public function address(): static
    {
        $this->estateOrAddress = 'addressids';

        return $this;
    }

    public function recordIdsAsEstate(): static
    {
        $this->estate();

        return $this;
    }

    public function recordIdsAsAddress(): static
    {
        $this->address();

        return $this;
    }
}
