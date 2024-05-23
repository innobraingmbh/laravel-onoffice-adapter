<?php

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Query\Concerns\NonFilterable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonOrderable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonSelectable;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class RegionBuilder extends Builder
{
    use NonFilterable;
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;

    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {
    }

    public function get(): Collection
    {
        $listLimit = $this->limit;
        $listOffset = $this->offset;

        return $this->onOfficeService->requestAll(/**
         * @throws OnOfficeException
         */ function () {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::Regions,
            );
        }, pageSize: $listLimit, offset: $listOffset);
    }

    /**
     * @throws OnOfficeException
     */
    public function first(): array
    {
        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Get,
            OnOfficeResourceType::Regions,
        );

        return $response->json('response.results.0.data.records.0');
    }

    /**
     * @throws OnOfficeException
     */
    public function find(int $id): array
    {
        throw new OnOfficeException('Method not implemented');
    }

    public function each(callable $callback): void
    {
        $listLimit = $this->limit;
        $listOffset = $this->offset;

        $this->onOfficeService->requestAllChunked(/**
         * @throws OnOfficeException
         */ function () {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::Regions,
            );
        }, $callback, pageSize: $listLimit, offset: $listOffset);
    }

    /**
     * @throws OnOfficeException
     */
    public function modify(int $id): bool
    {
        throw new OnOfficeException('Not implemented');
    }
}
