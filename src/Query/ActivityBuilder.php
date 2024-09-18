<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Query\Concerns\RecordIds;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class ActivityBuilder extends Builder
{
    use RecordIds;

    public string $estateOrAddress = 'estate';

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $orderBy = $this->getOrderBy();

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Activity,
            parameters: [
                $this->estateOrAddress => $this->recordIds,
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
            OnOfficeResourceType::Activity,
            parameters: [
                $this->estateOrAddress => $this->recordIds,
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
            OnOfficeAction::Get,
            OnOfficeResourceType::Activity,
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
            OnOfficeResourceType::Activity,
            parameters: [
                $this->estateOrAddress => $this->recordIds,
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
    public function create(array $data): array
    {
        $data = array_replace($data, [
            $this->estateOrAddress => $this->recordIds,
        ]);

        $request = new OnOfficeRequest(
            OnOfficeAction::Create,
            OnOfficeResourceType::Activity,
            parameters: $data,
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
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
