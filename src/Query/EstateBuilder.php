<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class EstateBuilder extends Builder
{
    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {}

    public function get(): Collection
    {
        return $this->onOfficeService->requestAll(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Read,
                OnOfficeResourceType::Estate,
                parameters: [
                    OnOfficeService::DATA => $this->columns,
                    OnOfficeService::FILTER => $this->getFilters(),
                    OnOfficeService::LISTLIMIT => $pageSize,
                    OnOfficeService::LISTOFFSET => $offset,
                    OnOfficeService::SORTBY => $this->getOrderBy(),
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
        $response = $this->onOfficeService->requestApi(
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

        return $response->json('response.results.0.data.records.0');
    }

    /**
     * @throws OnOfficeException
     */
    public function find(int $id): array
    {
        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
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
        $this->onOfficeService->requestAllChunked(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Read,
                OnOfficeResourceType::Estate,
                parameters: [
                    OnOfficeService::DATA => $this->columns,
                    OnOfficeService::FILTER => $this->getFilters(),
                    OnOfficeService::LISTLIMIT => $pageSize,
                    OnOfficeService::LISTOFFSET => $offset,
                    OnOfficeService::SORTBY => $this->getOrderBy(),
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
            OnOfficeResourceType::Estate,
            $id,
            parameters: [
                OnOfficeService::DATA => $this->modifies,
            ],
        );

        return true;
    }

    /**
     * @throws OnOfficeException
     */
    public function create(array $data): array
    {
        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Create,
            OnOfficeResourceType::Estate,
            parameters: [
                OnOfficeService::DATA => $data,
            ],
        );

        return $response->json('response.results.0.data.records.0');
    }
}
