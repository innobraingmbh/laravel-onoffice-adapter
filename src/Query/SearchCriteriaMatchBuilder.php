<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeQueryException;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonFilterable;
use Innobrain\OnOfficeAdapter\Query\Concerns\Paginate;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

/**
 * Finds the search criteria that match a set of field values, e.g. the ones a
 * listing would satisfy. Each record's elements hold the search criteria `Id`,
 * the linked `adresse` and the fields selected via select() or outputAll().
 *
 * The endpoint pages with `offset` and `limit` instead of `listoffset` and
 * `listlimit`, and returns one search criteria per address unless grouping is
 * turned off.
 */
class SearchCriteriaMatchBuilder extends Builder
{
    use NonFilterable;
    use Paginate;

    /**
     * @var array<string, mixed>
     */
    private array $searchData = [];

    private bool $outputAll = false;

    private bool $groupByAddress = true;

    /**
     * @param  array<string, mixed>  $searchData
     */
    public function searchData(array $searchData): static
    {
        $this->searchData = array_merge($this->searchData, $searchData);

        return $this;
    }

    public function outputAll(bool $outputAll = true): static
    {
        $this->outputAll = $outputAll;

        return $this;
    }

    public function groupByAddress(bool $groupByAddress = true): static
    {
        $this->groupByAddress = $groupByAddress;

        return $this;
    }

    /**
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function count(): int
    {
        $request = $this->buildReadRequest();
        data_set($request->parameters, OnOfficeService::LIMIT, 0);

        return $this->requestApi($request)->json(OnOfficeResponsePath::META_COUNT_ABSOLUTE, 0);
    }

    protected function buildReadRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Search,
            OnOfficeResourceId::SearchCriteria,
            parameters: [
                OnOfficeService::SEARCHDATA => $this->searchData,
                OnOfficeService::OUTPUTALL => $this->outputAll,
                OnOfficeService::OUTPUTFIELDS => $this->columns,
                OnOfficeService::GROUPBYADDRESS => $this->groupByAddress,
                OnOfficeService::ORDER => array_column($this->orderBy, 1, 0),
                ...$this->customParameters,
            ],
        );
    }

    /**
     * @throws OnOfficeQueryException
     */
    protected function buildFindRequest(int|string $id): OnOfficeRequest
    {
        throw new OnOfficeQueryException('Matching search criteria cannot be read by id. Use SearchCriteriaRepository::query()->mode(\'searchcriteria\')->find() instead.');
    }

    protected function applyListWindow(OnOfficeRequest $request, int $listLimit, int $offset): void
    {
        data_set($request->parameters, OnOfficeService::LIMIT, $listLimit);
        data_set($request->parameters, OnOfficeService::OFFSET, $offset);
    }
}
