<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeQueryException;
use Innobrain\OnOfficeAdapter\Query\Concerns\RecordIds;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Override;
use Throwable;

class SearchCriteriaBuilder extends Builder
{
    use RecordIds;

    private string $mode = 'internal';

    private int $addressId;

    /**
     * @param  int|array<int, int>  $id
     * @return array<string, mixed>|null
     *
     * @throws Throwable<OnOfficeException>
     */
    public function find(int|array $id): ?array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::GetSearchCriteria,
            parameters: [
                OnOfficeService::MODE => $this->mode,
                OnOfficeService::IDS => Arr::wrap($id),
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json(OnOfficeResponsePath::FIRST_RECORD, []);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     *
     * @throws Throwable<OnOfficeException>
     */
    #[Override]
    public function get(): Collection
    {
        if ($this->mode === 'filter') {
            return $this->getFiltered();
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::GetSearchCriteria,
            parameters: [
                OnOfficeService::MODE => $this->mode,
                OnOfficeService::IDS => $this->recordIds,
                ...$this->customParameters,
            ],
        );

        /** @var array<int, array<string, mixed>> $records */
        $records = $this->requestApi($request)->json(OnOfficeResponsePath::RECORDS, []);

        return collect($records);
    }

    /**
     * @return array<string, mixed>|null
     *
     * @throws Throwable<OnOfficeException>
     */
    #[Override]
    public function first(): ?array
    {
        throw_unless($this->mode === 'filter', OnOfficeQueryException::class, 'first() is only supported in filter mode. Use find() to read by id.');

        return (clone $this)->limit(1)->getFiltered()->first();
    }

    /**
     * The filter mode reports `meta.cntabsolute` as the number of records on the
     * page instead of the total, so pages are read until one comes back short.
     *
     * @return Collection<int, array<string, mixed>>
     *
     * @throws Throwable<OnOfficeException>
     */
    private function getFiltered(): Collection
    {
        throw_if($this->orderBy === [], OnOfficeQueryException::class, 'The filter mode requires a sort order, e.g. orderBy(\'creationdate\').');

        $records = collect();

        if ($this->limit === 0) {
            return $records;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::GetSearchCriteria,
            parameters: [
                OnOfficeService::MODE => 'filter',
                OnOfficeService::FILTER => $this->getFilters(),
                ...$this->getSplitSortParameters(),
                ...$this->customParameters,
            ],
        );

        $offset = $this->offset;

        do {
            $pageSize = $this->limit === -1 ? $this->pageSize : min($this->pageSize, $this->limit - $records->count());
            $this->applyListWindow($request, $pageSize, $offset);

            /** @var array<int, array<string, mixed>> $page */
            $page = $this->requestApi($request)->json(OnOfficeResponsePath::RECORDS, []);

            $records->push(...$page);
            $offset += $pageSize;
        } while (count($page) === $pageSize && ($this->limit === -1 || $records->count() < $this->limit));

        return $records;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws Throwable<OnOfficeException>
     */
    public function create(array $data): array
    {
        throw_unless(isset($this->addressId), OnOfficeQueryException::class, 'Address ID is required to create a search criteria');

        $request = new OnOfficeRequest(
            OnOfficeAction::Create,
            OnOfficeResourceType::SearchCriteria,
            parameters: [
                OnOfficeService::ADDRESSID => $this->addressId,
                OnOfficeService::DATA => $data,
                ...$this->customParameters,
            ],
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
            OnOfficeResourceType::SearchCriteria,
            $id,
            parameters: [
                OnOfficeService::DATA => $this->modifies,
                ...$this->customParameters,
            ],
        );

        $this->requestApi($request);

        return true;
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function delete(int $id): bool
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Delete,
            OnOfficeResourceType::SearchCriteria,
            $id,
            parameters: $this->customParameters,
        );

        return $this->requestApi($request)
            ->json(OnOfficeResponsePath::FIRST_RECORD_ELEMENTS_SUCCESS) === 'success';
    }

    public function mode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function addressId(int $addressId): self
    {
        $this->addressId = $addressId;

        return $this;
    }
}
