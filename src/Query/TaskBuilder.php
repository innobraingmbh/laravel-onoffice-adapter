<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\Paginate;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class TaskBuilder extends Builder
{
    use Paginate;

    /**
     * The task read endpoint reports `meta.cntabsolute` as the number of rows
     * actually returned (i.e. min(listlimit, total)), not a true absolute count.
     * It also accepts no listoffset, so the largest count it can report is the
     * API's maximum page size.
     */
    private const MAX_LIST_LIMIT = 500;

    public ?int $relatedAddressId = null;

    public ?int $relatedEstateId = null;

    public ?int $relatedProjectId = null;

    /**
     * The task endpoint rejects listoffset outright ("Invalid field in input
     * data: listoffset"), so reads never send it and are bounded to a single
     * listlimit page (max 500).
     */
    protected bool $supportsListOffset = false;

    /**
     * Count matching tasks.
     *
     * Unlike other resources, the task endpoint's `meta.cntabsolute` equals the
     * number of records returned for the given listlimit rather than the real
     * total, so the inherited count() (which forces listlimit=1) always returns
     * 1. Requesting the maximum page size makes cntabsolute reflect the true
     * total, capped at MAX_LIST_LIMIT when more tasks exist.
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function count(): int
    {
        $request = $this->buildReadRequest();
        data_set($request->parameters, OnOfficeService::DATA, []);
        data_set($request->parameters, OnOfficeService::LISTLIMIT, self::MAX_LIST_LIMIT);

        return $this->requestApi($request)->json('response.results.0.data.meta.cntabsolute', 0);
    }

    protected function buildReadRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Task,
            parameters: array_filter([
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                'relatedAddressId' => $this->relatedAddressId,
                'relatedEstateId' => $this->relatedEstateId,
                'relatedProjectIds' => $this->relatedProjectId,
                ...$this->customParameters,
            ], fn ($v) => ! is_null($v)),
        );
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function find(int $id): ?array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Task,
            $id,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws Throwable<OnOfficeException>
     */
    public function create(array $data): array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Create,
            OnOfficeResourceType::Task,
            parameters: array_filter([
                OnOfficeService::DATA => $data,
                'relatedAddressId' => $this->relatedAddressId,
                'relatedEstateId' => $this->relatedEstateId,
                'relatedProjectIds' => $this->relatedProjectId,
                ...$this->customParameters,
            ], fn ($v) => ! is_null($v)),
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function modify(int $id): bool
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Modify,
            OnOfficeResourceType::Task,
            $id,
            parameters: array_filter([
                OnOfficeService::DATA => $this->modifies,
                'relatedAddressId' => $this->relatedAddressId,
                'relatedEstateId' => $this->relatedEstateId,
                'relatedProjectIds' => $this->relatedProjectId,
                ...$this->customParameters,
            ], fn ($v) => ! is_null($v)),
        );

        $this->requestApi($request);

        return true;
    }

    public function relatedAddress(int $id): static
    {
        $this->relatedAddressId = $id;

        return $this;
    }

    public function relatedEstate(int $id): static
    {
        $this->relatedEstateId = $id;

        return $this;
    }

    public function relatedProject(int $id): static
    {
        $this->relatedProjectId = $id;

        return $this;
    }
}
