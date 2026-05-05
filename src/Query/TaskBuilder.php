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

    public ?int $relatedAddressId = null;

    public ?int $relatedEstateId = null;

    public ?int $relatedProjectId = null;

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
