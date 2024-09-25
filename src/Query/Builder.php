<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Exceptions\StrayRequestException;
use Innobrain\OnOfficeAdapter\Query\Concerns\BuilderInterface;
use Innobrain\OnOfficeAdapter\Repositories\BaseRepository;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use JsonException;
use Throwable;

class Builder implements BuilderInterface
{
    use Conditionable;

    /**
     * An array of columns to be selected.
     */
    public array $columns = [];

    /**
     * An array of filters.
     */
    public array $filters = [];

    /**
     * An array of modify parameters.
     */
    public array $modifies = [];

    /**
     * The limit for the result.
     */
    public int $limit = -1;

    /**
     * The page size for the result.
     */
    public int $pageSize = 500;

    /**
     * An array of columns to order by.
     * Each element should be an array with the column name and the direction.
     */
    public array $orderBy = [];

    /**
     * The offset for the query.
     */
    public int $offset = 0;

    /**
     * An array of custom parameters.
     */
    public array $customParameters = [];

    /**
     * The stub callables that will be used to fake the responses.
     */
    protected Collection $stubCallables;

    /**
     * Indicates that an exception should be thrown
     * if a request is made without a stub callable.
     */
    protected bool $preventStrayRequests = false;

    /**
     * The OnOffice service.
     */
    protected OnOfficeService $onOfficeService;

    /**
     * The repository that created the builder.
     */
    protected BaseRepository $repository;

    public function __construct() {}

    protected function getOnOfficeService(): OnOfficeService
    {
        return $this->onOfficeService ?? $this->createOnOfficeService();
    }

    protected function createOnOfficeService(): OnOfficeService
    {
        return app(OnOfficeService::class);
    }

    public function setRepository(BaseRepository $repository): static
    {
        $this->repository = $repository;

        return $this;
    }

    public function stub(Collection $stubCallable): self
    {
        $this->stubCallables = $stubCallable;

        return $this;
    }

    public function preventStrayRequests(bool $value = true): static
    {
        $this->preventStrayRequests = $value;

        return $this;
    }

    /**
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function requestApi(OnOfficeRequest $request): Response
    {
        $response = $this->getStubCallable($request);

        if (is_null($response)) {
            throw_if($this->preventStrayRequests, new StrayRequestException(request: $request));

            $response = $this->getOnOfficeService()->requestApi(
                $request->actionId,
                $request->resourceType,
                $request->resourceId,
                $request->identifier,
                $request->parameters,
            );
        }

        $this->repository->recordRequestResponsePair($request, $response->json());

        return $response;
    }

    /**
     * @throws OnOfficeException
     */
    protected function requestAll(OnOfficeRequest $request): Collection
    {
        return $this->getOnOfficeService()->requestAll(/**
         * @throws OnOfficeException
         * @throws Throwable
         */ function (int $pageSize, int $offset) use ($request) {
            data_set($request->parameters, OnOfficeService::LISTLIMIT, $pageSize);
            data_set($request->parameters, OnOfficeService::LISTOFFSET, $offset);

            return $this->requestApi($request);
        }, pageSize: $this->pageSize, offset: $this->offset, limit: $this->limit);
    }

    /**
     * @throws OnOfficeException
     */
    protected function requestAllChunked(OnOfficeRequest $request, callable $callback): void
    {
        $this->getOnOfficeService()->requestAllChunked(/**
         * @throws OnOfficeException
         * @throws Throwable
         */ function (int $pageSize, int $offset) use ($request) {
            data_set($request->parameters, OnOfficeService::LISTLIMIT, $pageSize);
            data_set($request->parameters, OnOfficeService::LISTOFFSET, $offset);

            return $this->requestApi($request);
        }, $callback, pageSize: $this->pageSize, offset: $this->offset, limit: $this->limit);
    }

    /**
     * @throws JsonException
     */
    protected function getStubCallable(OnOfficeRequest $request): ?Response
    {
        $response = ($this->stubCallables ?? collect())->first();

        ($this->stubCallables ?? collect())->shift();

        if (is_null($response)) {
            return null;
        }

        /** @var OnOfficeResponse $response */
        if ($response->isEmpty()) {
            return null;
        }

        $response = $response->shift()->toResponse();

        $response = new Psr7Response(200, [], json_encode($response, JSON_THROW_ON_ERROR));

        return new Response($response);
    }

    public function select(array|string $columns = ['ID']): static
    {
        $this->columns = Arr::wrap($columns);

        return $this;
    }

    public function addSelect(array|string $column): static
    {
        $column = Arr::wrap($column);

        $this->columns = array_merge($this->columns, $column);

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $direction = Str::upper($direction);

        $this->orderBy[] = [$column, $direction];

        return $this;
    }

    public function orderByDesc(string $column): static
    {
        return $this->orderBy($column, 'desc');
    }

    public function addModify(string|array $column, mixed $value = null): static
    {
        if (is_array($column)) {
            $this->modifies = array_merge($this->modifies, $column);

            return $this;
        }

        $this->modifies[$column] = $value;

        return $this;
    }

    public function offset(int $value): static
    {
        $this->offset = max(0, $value);

        return $this;
    }

    /**
     * Be aware that the limit is capped at 500.
     */
    public function limit(int $value): static
    {
        $this->limit = max(-1, $value);

        return $this;
    }

    /**
     * Be aware that the page size is capped at 500.
     */
    public function pageSize(int $value): static
    {
        $this->pageSize = min(500, $value);
        $this->pageSize = max(1, $this->pageSize);

        return $this;
    }

    public function where(string $column, mixed $operator, mixed $value = null): static
    {
        if (is_null($value)) {
            $value = $operator;
            $operator = '=';
        }

        $this->filters[$column][] = [$operator, $value];

        return $this;
    }

    protected function getFilters(): array
    {
        return collect($this->filters)->mapWithKeys(function (array $value, string $column) {
            return [
                $column => collect($value)->map(function ($filter) {
                    [$operator, $value] = $filter;

                    return [
                        'op' => $operator,
                        'val' => $value,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    protected function getOrderBy(): array
    {
        return collect($this->orderBy)->mapWithKeys(function ($value) {
            [$column, $direction] = $value;

            return [
                $column => $direction,
            ];
        })->toArray();
    }

    public function parameter(string $key, mixed $value): static
    {
        $this->customParameters[$key] = $value;

        return $this;
    }

    public function parameters(array $parameters): static
    {
        $this->customParameters = array_replace_recursive($this->customParameters, $parameters);

        return $this;
    }

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        throw new OnOfficeException('Not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function call(OnOfficeRequest $request): Collection
    {
        return $this->requestAll($request);
    }

    /**
     * @throws OnOfficeException
     */
    public function first(): ?array
    {
        throw new OnOfficeException('Not implemented');
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function once(OnOfficeRequest $request): Response
    {
        return $this->requestApi($request);
    }

    /**
     * @throws OnOfficeException
     */
    public function find(int $id): array
    {
        throw new OnOfficeException('Not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        throw new OnOfficeException('Not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function chunked(OnOfficeRequest $request, callable $callback): void
    {
        $this->requestAllChunked($request, $callback);
    }

    /**
     * @throws OnOfficeException
     */
    public function modify(int $id): bool
    {
        throw new OnOfficeException('Not implemented');
    }
}
