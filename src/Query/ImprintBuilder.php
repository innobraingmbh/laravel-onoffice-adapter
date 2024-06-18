<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Query\Concerns\NonFilterable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonOrderable;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class ImprintBuilder extends Builder
{
    use NonFilterable;
    use NonFilterable;
    use NonOrderable;

    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {
    }

    public function get(): Collection
    {
        $columns = $this->columns;
        $listLimit = $this->limit;
        $listOffset = $this->offset;

        return $this->onOfficeService->requestAll(/**
         * @throws OnOfficeException
         */ function () use ($columns) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Read,
                OnOfficeResourceType::Impressum,
                parameters: [
                    OnOfficeService::DATA => $columns,
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

        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Read,
            OnOfficeResourceType::Impressum,
            parameters: [
                OnOfficeService::DATA => $columns,
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
            OnOfficeAction::Read,
            OnOfficeResourceType::Impressum,
            resourceId: $id,
            parameters: [
                OnOfficeService::DATA => $columns,
            ]
        );

        return $response->json('response.results.0.data.records.0');
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        throw new OnOfficeException('Not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function modify(int $id): bool
    {
        throw new OnOfficeException('Not implemented');
    }
}
