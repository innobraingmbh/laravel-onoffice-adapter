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
            ],
        );

        return $this->requestApi($request)
            ->json(OnOfficeResponsePath::FIRST_RECORD);
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
