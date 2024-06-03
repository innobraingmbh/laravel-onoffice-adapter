<?php

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class EstateFileBuilder extends Builder
{
    public int $estateId;

    public function __construct(
        private readonly OnOfficeService $onOfficeService,
        int $estateId,
    ) {
        $this->estateId = $estateId;
    }

    public function get(): Collection
    {
        return $this->onOfficeService->requestAll(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::File,
                OnOfficeResourceId::Estate,
                parameters: [
                    'estateid' => $this->estateId,
                    OnOfficeService::LISTLIMIT => $pageSize,
                    OnOfficeService::LISTOFFSET => $offset,
                ],
            );
        }, pageSize: $this->limit, offset: $this->offset);
    }

    /**
     * @throws OnOfficeException
     */
    public function first(): array
    {
        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Get,
            OnOfficeResourceType::File,
            OnOfficeResourceId::Estate,
            parameters: [
                'estateid' => $this->estateId,
            ],
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
            OnOfficeResourceType::File,
            OnOfficeResourceId::Estate,
            parameters: [
                'estateid' => $this->estateId,
                'fileid' => $id,
            ],
        );

        $result = $response->json('response.results.0.data.records.0');

        if (! $result) {
            throw new OnOfficeException('File not found');
        }

        return $result;
    }

    public function each(callable $callback): void
    {
        $this->onOfficeService->requestAllChunked(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::File,
                OnOfficeResourceId::Estate,
                parameters: [
                    'estateid' => $this->estateId,
                    OnOfficeService::LISTLIMIT => $pageSize,
                    OnOfficeService::LISTOFFSET => $offset,
                ],
            );
        }, $callback, pageSize: $this->limit, offset: $this->offset);
    }

    /**
     * @throws OnOfficeException
     */
    public function modify(int $id): bool
    {
        throw new OnOfficeException('Not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function delete(int $id): bool
    {
        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Delete,
            OnOfficeResourceType::FileRelation,
            parameters: [
                'fileId' => $id,
                'parentid' => $this->estateId,
                'relationtype' => 'estate',
            ],
        );

        return $response->json('response.results.0.data.records.0.elements.success') === 'success';
    }
}
