<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\RecordIds;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class ActivityBuilder extends Builder
{
    use RecordIds;

    public string $estateOrAddress = 'estate';

    public ?int $estateId = null;

    public array $addressIds = [];

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
                ...$this->prepareEstateOrAddressParameters(),
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
                ...$this->prepareEstateOrAddressParameters(),
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
    public function find(int $id): ?array
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
                ...$this->prepareEstateOrAddressParameters(),
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
            ...$this->prepareEstateOrAddressParameters(),
        ]);

        $request = new OnOfficeRequest(
            OnOfficeAction::Create,
            OnOfficeResourceType::Activity,
            parameters: $data,
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * @deprecated Use estateId() instead
     */
    public function estate(): static
    {
        $this->estateOrAddress = 'estateid';

        return $this;
    }

    /**
     * @deprecated Use addressIds() instead
     */
    public function address(): static
    {
        $this->estateOrAddress = 'addressids';

        return $this;
    }

    /**
     * @deprecated Use estateId() instead
     */
    public function recordIdsAsEstate(): static
    {
        $this->estate();

        return $this;
    }

    /**
     * @deprecated Use addressIds() instead
     */
    public function recordIdsAsAddress(): static
    {
        $this->address();

        return $this;
    }

    public function estateId(int $estateId): static
    {
        $this->estateId = $estateId;

        return $this;
    }

    public function addressIds(int|array $addressIds): static
    {
        $this->addressIds = Arr::wrap($addressIds);

        return $this;
    }

    /**
     * Function is used to deprecate the usage of recordIdsAsEstate() and recordIdsAsAddress()
     * without breaking changes.
     */
    private function prepareEstateOrAddressParameters(): array
    {
        $parameters = [$this->estateOrAddress => $this->recordIds];

        // If the estateOrAddress is set to estate, we know the user has not used the old methods.
        if ($this->estateOrAddress === 'estate') {
            $parameters = [];
        }

        if (! is_null($this->estateId)) {
            $parameters['estateid'] = $this->estateId;
        }

        if ($this->addressIds !== []) {
            $parameters['addressids'] = $this->addressIds;
        }

        return $parameters;
    }
}
