<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\Paginate;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
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
     * Unlike other resources, the task endpoint's `meta.cntabsolute` equals the
     * number of records returned for the given listlimit rather than the real
     * total, so counting at the inherited listlimit=1 always returns 1.
     * Requesting the maximum page size makes cntabsolute reflect the true
     * total, capped at MAX_LIST_LIMIT when more tasks exist.
     */
    protected function countListLimit(): int
    {
        return self::MAX_LIST_LIMIT;
    }

    protected function buildReadRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Task,
            parameters: array_filter([
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                ...$this->relatedParameters(),
                ...$this->customParameters,
            ], fn ($v) => ! is_null($v)),
        );
    }

    protected function buildFindRequest(int|string $id): OnOfficeRequest
    {
        return $this->singleRecordRequest(OnOfficeAction::Read, OnOfficeResourceType::Task, $id);
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
                ...$this->relatedParameters(),
                ...$this->customParameters,
            ], fn ($v) => ! is_null($v)),
        );

        return $this->requestApi($request)
            ->json(OnOfficeResponsePath::FIRST_RECORD);
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
                ...$this->relatedParameters(),
                ...$this->customParameters,
            ], fn ($v) => ! is_null($v)),
        );

        $this->requestApi($request);

        return true;
    }

    /**
     * The relation parameters shared by the task read, create and modify
     * requests. Null entries are stripped by the caller's array_filter.
     *
     * @return array<string, int|null>
     */
    private function relatedParameters(): array
    {
        return [
            OnOfficeService::RELATEDADDRESSID => $this->relatedAddressId,
            OnOfficeService::RELATEDESTATEID => $this->relatedEstateId,
            OnOfficeService::RELATEDPROJECTIDS => $this->relatedProjectId,
        ];
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
