<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\Paginate;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class ActivityBuilder extends Builder
{
    use Paginate;

    public ?int $estateId = null;

    /**
     * @var array<int, int>
     */
    public array $addressIds = [];

    protected function buildReadRequest(): OnOfficeRequest
    {
        $orderBy = $this->getOrderBy();

        return new OnOfficeRequest(
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
    }

    protected function buildFindRequest(int|string $id): OnOfficeRequest
    {
        return $this->singleRecordRequest(OnOfficeAction::Get, OnOfficeResourceType::Activity, $id);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws Throwable<OnOfficeException>
     */
    public function create(array $data): array
    {
        $data = array_replace($data, [
            ...$this->prepareEstateOrAddressParameters(create: true),
        ]);

        $request = new OnOfficeRequest(
            OnOfficeAction::Create,
            OnOfficeResourceType::Activity,
            parameters: $data,
        );

        return $this->requestApi($request)
            ->json(OnOfficeResponsePath::FIRST_RECORD);
    }

    public function estateId(int $estateId): static
    {
        $this->estateId = $estateId;

        return $this;
    }

    /**
     * @param  int|array<int, int>  $addressIds
     */
    public function addressIds(int|array $addressIds): static
    {
        $this->addressIds = Arr::wrap($addressIds);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareEstateOrAddressParameters(bool $create = false): array
    {
        $parameters = [];

        if (! is_null($this->estateId)) {
            $parameters[OnOfficeService::ESTATEID] = $this->estateId;
        }

        if ($this->addressIds !== []) {
            $key = match ($create) {
                true => OnOfficeService::ADDRESSIDS,
                false => OnOfficeService::ADDRESSID,
            };

            $parameters[$key] = $this->addressIds;
        }

        return $parameters;
    }
}
