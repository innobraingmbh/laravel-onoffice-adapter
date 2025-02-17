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

class LogBuilder extends Builder
{
    public string $module = '';

    public string $action = '';

    public int $userId = -1;

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $parameters = [
            OnOfficeService::MODULE => $this->module,
            OnOfficeService::ACTION => $this->action,
            OnOfficeService::FILTER => $this->getFilters(),
            OnOfficeService::LISTLIMIT => $this->limit,
            OnOfficeService::LISTOFFSET => $this->offset,
            ...$this->customParameters,
        ];

        if ($this->userId > 0) {
            $parameters['user'] = $this->userId;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Log,
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
            OnOfficeService::ACTION => $this->action,
            OnOfficeService::FILTER => $this->getFilters(),
            OnOfficeService::LISTLIMIT => $this->limit,
            OnOfficeService::LISTOFFSET => $this->offset,
            ...$this->customParameters,
        ];

        if ($this->userId > 0) {
            $parameters['user'] = $this->userId;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Log,
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
        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Log,
            $id,
            parameters: $this->customParameters,
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        $parameters = [
            OnOfficeService::MODULE => $this->module,
            OnOfficeService::ACTION => $this->action,
            OnOfficeService::FILTER => $this->getFilters(),
            OnOfficeService::LISTLIMIT => $this->limit,
            OnOfficeService::LISTOFFSET => $this->offset,
            ...$this->customParameters,
        ];

        if ($this->userId > 0) {
            $parameters['user'] = $this->userId;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Log,
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
        $parameters = [
            OnOfficeService::MODULE => $this->module,
            OnOfficeService::ACTION => $this->action,
            OnOfficeService::FILTER => $this->getFilters(),
            OnOfficeService::LISTLIMIT => $this->limit,
            OnOfficeService::LISTOFFSET => $this->offset,
            ...$this->customParameters,
        ];

        if ($this->userId > 0) {
            $parameters['user'] = $this->userId;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Address,
            parameters: $parameters
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.meta.cntabsolute', 0);
    }

    public function withModule(string $module): static
    {
        $this->module = $module;

        return $this;
    }

    public function withAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function withUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
}
