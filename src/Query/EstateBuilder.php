<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\Input;
use Innobrain\OnOfficeAdapter\Query\Concerns\Paginate;
use Innobrain\OnOfficeAdapter\Query\Concerns\Searchable;
use Innobrain\OnOfficeAdapter\Query\Concerns\WritesRecords;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class EstateBuilder extends Builder
{
    use Input;
    use Paginate;
    use Searchable;
    use WritesRecords;

    protected function buildReadRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                ...$this->getSortByParameter(),
                ...$this->customParameters,
            ]
        );
    }

    protected function buildFindRequest(int|string $id): OnOfficeRequest
    {
        return $this->singleRecordRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate, $id);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function modify(int $id): bool
    {
        return $this->modifyRecord(OnOfficeResourceType::Estate, [
            OnOfficeService::DATA => $this->modifies,
            ...$this->customParameters,
        ], $id);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws Throwable<OnOfficeException>
     */
    public function create(array $data): array
    {
        return $this->createRecord(OnOfficeResourceType::Estate, [
            OnOfficeService::DATA => $data,
            ...$this->customParameters,
        ]);
    }

    protected function searchResourceId(): OnOfficeResourceId
    {
        return OnOfficeResourceId::Estate;
    }
}
