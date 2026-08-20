<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
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
        return $this->requestAll($this->buildReadRequest());
    }

    /**
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function first(): ?array
    {
        return $this->requestApi($this->buildReadRequest())
            ->json(OnOfficeResponsePath::FIRST_RECORD);
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
            ->json(OnOfficeResponsePath::FIRST_RECORD);
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        $this->requestAllChunked($this->buildReadRequest(), $callback);
    }

    /**
     * Returns the number of records that match the query. This number is from the API
     * and might be lower than the actual number of records when queried with get().
     *
     * @throws Throwable<OnOfficeException>
     */
    public function count(): int
    {
        return $this->requestApi($this->buildReadRequest())
            ->json(OnOfficeResponsePath::META_COUNT_ABSOLUTE, 0);
    }

    /**
     * Build the list read request shared by get(), first(), each() and count().
     */
    protected function buildReadRequest(): OnOfficeRequest
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
            $parameters[OnOfficeService::USER] = $this->userId;
        }

        return new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Log,
            parameters: $parameters,
        );
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
