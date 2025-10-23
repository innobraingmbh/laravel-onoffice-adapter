<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class LastSeenBuilder extends Builder
{
    public string $module = '';

    public int $userId = -1;

    /**
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function get(): Collection
    {
        $parameters = [
            OnOfficeService::MODULE => $this->module,
            OnOfficeService::FILTER => $this->getFilters(),
            OnOfficeService::LISTLIMIT => $this->limit,
            ...$this->customParameters,
        ];

        if ($this->userId > 0) {
            $parameters['user'] = $this->userId;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::RecordsLastSeen,
            parameters: $parameters,
        );

        return $this->requestAll($request);
    }

    /**
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function first(): ?array
    {
        $parameters = [
            OnOfficeService::MODULE => $this->module,
            OnOfficeService::FILTER => $this->getFilters(),
            OnOfficeService::LISTLIMIT => 1,
            ...$this->customParameters,
        ];

        if ($this->userId > 0) {
            $parameters['user'] = $this->userId;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::RecordsLastSeen,
            parameters: $parameters,
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function find(int $id): ?array
    {
        throw new OnOfficeException('Find by ID is not supported for LastSeen records.');
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        $parameters = [
            OnOfficeService::MODULE => $this->module,
            OnOfficeService::FILTER => $this->getFilters(),
            OnOfficeService::LISTLIMIT => $this->limit,
            ...$this->customParameters,
        ];

        if ($this->userId > 0) {
            $parameters['user'] = $this->userId;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::RecordsLastSeen,
            parameters: $parameters,
        );

        $this->requestAllChunked($request, $callback);
    }

    /**
     * Returns the number of records that match the query. This number is from the API
     * and might be lower than the actual number of records when queried with get().
     *
     * @throws Throwable<OnOfficeException>
     */
    public function count(): int
    {
        throw new OnOfficeException('Count is not supported for LastSeen records.');
    }

    public function withModule(string $module): static
    {
        $this->module = $module;

        return $this;
    }

    public function withUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
}
