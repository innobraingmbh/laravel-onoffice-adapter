<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\Paginate;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class AppointmentBuilder extends Builder
{
    use Paginate;

    public ?string $startDate = null;

    public ?string $endDate = null;

    /** @var array<int, int> */
    public array $userIds = [];

    /** @var array<int, int> */
    public array $groupIds = [];

    public ?bool $showCancelled = null;

    public ?bool $showDone = null;

    public ?bool $showRecurrent = null;

    public function dateRange(string $startDate, string $endDate): static
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @param  array<int, int>|int  $userIds
     */
    public function users(array|int $userIds): static
    {
        $this->userIds = Arr::wrap($userIds);

        return $this;
    }

    /**
     * @param  array<int, int>|int  $groupIds
     */
    public function groups(array|int $groupIds): static
    {
        $this->groupIds = Arr::wrap($groupIds);

        return $this;
    }

    public function cancelled(bool $show = true): static
    {
        $this->showCancelled = $show;

        return $this;
    }

    public function done(bool $show = true): static
    {
        $this->showDone = $show;

        return $this;
    }

    public function recurrent(bool $show = true): static
    {
        $this->showRecurrent = $show;

        return $this;
    }

    protected function buildReadRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::AppointmentList,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->buildAppointmentListFilter(),
                ...$this->customParameters,
            ],
        );
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function find(int $id): ?array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Calendar,
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
     * @throws Throwable<OnOfficeException>
     */
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Create,
            OnOfficeResourceType::Calendar,
            parameters: [
                ...$data,
                ...$this->customParameters,
            ],
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
            OnOfficeResourceType::Calendar,
            $id,
            parameters: [
                OnOfficeService::DATA => $this->modifies,
                ...$this->customParameters,
            ],
        );

        $response = $this->requestApi($request);

        return $response->json('response.results.0.data.records.0.elements') !== null;
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function delete(int $id): bool
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Delete,
            OnOfficeResourceType::Calendar,
            $id,
            parameters: [
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0.elements.success') === 'success';
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    /**
     * @param  array<string, mixed>  $data
     * @return array<int, mixed>
     */
    public function conflicts(array $data): array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::AppointmentConflicts,
            parameters: [
                ...$data,
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records', []);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    /**
     * @return array<int, mixed>
     */
    public function sendConfirmation(int $calendarId, bool $useDefaultMailAccount = false): array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Do,
            OnOfficeResourceType::AppointmentAffirmation,
            parameters: [
                'calendarId' => $calendarId,
                'useDefaultMailAccount' => $useDefaultMailAccount,
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records', []);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    /**
     * @param  array<string, mixed>|null  $filter
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function resources(?array $filter = null): \Illuminate\Support\Collection
    {
        $parameters = [...$this->customParameters];

        if ($filter !== null) {
            $parameters[OnOfficeService::FILTER] = $filter;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::CalendarResources,
            parameters: $parameters,
        );

        return $this->requestAll($request);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAppointmentListFilter(): array
    {
        $filter = [];

        if ($this->startDate !== null) {
            $filter['startDate'] = $this->startDate;
        }

        if ($this->endDate !== null) {
            $filter['endDate'] = $this->endDate;
        }

        if ($this->userIds !== []) {
            $filter['userIds'] = $this->userIds;
        }

        if ($this->groupIds !== []) {
            $filter['groupIds'] = $this->groupIds;
        }

        if ($this->showCancelled !== null) {
            $filter['isCancelled'] = $this->showCancelled;
        }

        if ($this->showDone !== null) {
            $filter['isDone'] = $this->showDone;
        }

        if ($this->showRecurrent !== null) {
            $filter['isRecurrent'] = $this->showRecurrent;
        }

        return array_merge($filter, $this->getFilters());
    }
}
