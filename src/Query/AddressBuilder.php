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
use Innobrain\OnOfficeAdapter\Query\Concerns\RecordIds;
use Innobrain\OnOfficeAdapter\Query\Concerns\Searchable;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class AddressBuilder extends Builder
{
    use Input;
    use Paginate;
    use RecordIds;
    use Searchable;

    protected function buildReadRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Address,
            parameters: [
                OnOfficeService::RECORDIDS => $this->recordIds,
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                ...$this->getSplitSortParameters(),
                ...$this->customParameters,
            ],
        );
    }

    protected function buildFindRequest(int|string $id): OnOfficeRequest
    {
        return $this->singleRecordRequest(OnOfficeAction::Read, OnOfficeResourceType::Address, $id);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function modify(int $id): bool
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Modify,
            OnOfficeResourceType::Address,
            $id,
            parameters: $this->modifies,
        );

        $this->requestApi($request);

        return true;
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
            OnOfficeResourceType::Address,
            parameters: $data,
        );

        return $this->requestApi($request)
            ->json(OnOfficeResponsePath::FIRST_RECORD);
    }

    protected function searchResourceId(): OnOfficeResourceId
    {
        return OnOfficeResourceId::Address;
    }

    public function addCountryIsoCodeType(string $countryIsoCodeType): static
    {
        $this->customParameters['countryIsoCodeType'] = $countryIsoCodeType;

        return $this;
    }
}
