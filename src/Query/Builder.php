<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeApiCredentials;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Exceptions\StrayRequestException;
use Innobrain\OnOfficeAdapter\Facades\BaseRepository as BaseRepositoryFacade;
use Innobrain\OnOfficeAdapter\Query\Concerns\BuilderInterface;
use Innobrain\OnOfficeAdapter\Repositories\BaseRepository;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use JsonException;
use Symfony\Component\VarDumper\VarDumper;
use Throwable;

class Builder implements BuilderInterface
{
    use Conditionable;

    /**
     * An array of columns to be selected.
     *
     * @var array<int, string>
     */
    public array $columns = [];

    /**
     * An array of filters.
     *
     * @var array<string, array<int, array{0: string, 1: mixed}>>
     */
    public array $filters = [];

    /**
     * An array of modify parameters.
     *
     * @var array<string, mixed>
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
     *
     * @var array<int, array{0: string, 1: string}>
     */
    public array $orderBy = [];

    /**
     * The offset for the query.
     */
    public int $offset = 0;

    /**
     * An array of custom parameters.
     *
     * @var array<string, mixed>
     */
    public array $customParameters = [];

    /**
     * The stub callables that will be used to fake the responses.
     *
     * @var Collection<int, OnOfficeResponse>
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

    /**
     * The before sending middlewares.
     *
     * @var array<int, callable>
     */
    protected array $beforeSendingCallbacks = [];

    /**
     * The after sending middlewares.
     *
     * @var array<int, callable|array<int, mixed>>
     */
    protected array $afterSendingCallbacks = [];

    /**
     * The last request that was made. Needed for the stubbing.
     */
    private ?OnOfficeRequest $requestCache = null;

    /**
     * The last stub response.
     */
    private ?OnOfficeResponse $responseCache = null;

    public function __construct(
        protected ?OnOfficeApiCredentials $credentials = null
    ) {}

    public function withCredentials(string|OnOfficeApiCredentials $token, string $secret = '', string $apiClaim = ''): static
    {
        if ($token instanceof OnOfficeApiCredentials) {
            $this->credentials = $token;

            return $this;
        }

        $this->credentials = new OnOfficeApiCredentials(token: $token, secret: $secret, apiClaim: $apiClaim);

        return $this;
    }

    protected function getOnOfficeService(): OnOfficeService
    {
        return tap($this->onOfficeService ?? $this->createOnOfficeService(),
            fn (OnOfficeService $service) => $service->setCredentials($this->credentials)
        );
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

    /**
     * @param  Collection<int, OnOfficeResponse>  $stubCallable
     */
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

    public function before(callable $callback): static
    {
        $this->beforeSendingCallbacks[] = $callback;

        return $this;
    }

    /**
     * If $callback is an array, the first element
     * is considered the callable, and the rest are parameters to be passed to the callable.
     *
     * @param  callable|array<int, mixed>  $callback
     * @return $this
     */
    public function after(callable|array $callback): static
    {
        $this->afterSendingCallbacks[] = $callback;

        return $this;
    }

    public function dump(): static
    {
        return $this->before(static function (OnOfficeRequest $request) {
            VarDumper::dump($request);
        });
    }

    public function dd(): static
    {
        return $this->before(static function (OnOfficeRequest $request) {
            VarDumper::dump($request);

            exit(1);
        });
    }

    public function raw(): static
    {
        return $this->before(static function (OnOfficeRequest $request) {
            VarDumper::dump($request->toRequestArray());

            exit(1);
        });
    }

    /**
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function requestApi(OnOfficeRequest $request): Response
    {
        $request = $this->runBeforeSendingCallbacks($request);

        $response = $this->getStubCallable($request);

        if (is_null($response)) {
            throw_if($this->preventStrayRequests, new StrayRequestException(request: $request));

            $response = $this->getOnOfficeService()->requestApi($request);
        } else {
            $this->getOnOfficeService()->throwIfResponseIsFailed($response);
        }

        $this->repository->recordRequestResponsePair($request, $response->json());

        return $this->runAfterSendingCallbacks($response);
    }

    protected function runBeforeSendingCallbacks(OnOfficeRequest $request): OnOfficeRequest
    {
        return tap($request, function (OnOfficeRequest &$request) {
            collect($this->beforeSendingCallbacks)->each(function (callable $callback) use (&$request) {
                $result = $callback($request);

                if ($result instanceof OnOfficeRequest) {
                    $request = $result;
                }
            });
        });
    }

    protected function runAfterSendingCallbacks(Response $response): Response
    {
        return tap($response, function (Response &$response) {
            collect($this->afterSendingCallbacks)->each(function (callable|array $callback) use (&$response) {
                // if the callback is an array, we assume the first element is the callable
                // and the rest are parameters to be passed to the callable
                $params = [];
                if (is_array($callback)) {
                    $params = $callback;
                    array_shift($params);
                    $callback = $callback[0];
                }
                $result = $callback($response, ...$params);

                if ($result instanceof Response) {
                    $response = $result;
                }
            });
        });
    }

    /**
     * Will check for each record in the response if the user has the right to access it.
     * Will remove every record that the user does not have access to from the response.
     * Checks for each record in the response if the user has the right to access it.
     * Removes every record that the user does not have access to from the response,
     * but does not change anything else in the response (e.g. count_absolute).
     *
     * @param  string  $action  The action to check rights for (e.g. 'get', 'edit').
     * @param  string  $module  The module name to check rights in (e.g. 'estate', 'address').
     * @param  int  $userId  The ID of the user whose rights are being checked.
     * @param  string  $resultPath  The dot-notated path to the records in the response body.
     *                              Defaults to 'response.results.0.data.records'.
     * @return self Returns the current Builder instance for method chaining.
     */
    public function checkUserRecordsRight(string $action, string $module, int $userId, string $resultPath = 'response.results.0.data.records'): self
    {
        return $this->after([
            function (Response $response, string $action, string $module, int $userId) use ($resultPath): ?Response {
                if ($response->failed()) {
                    return null;
                }

                $ids = $response->json('response.results.0.data.records.*.id', []);

                if ($ids === []) {
                    $responseBody = $response->json();
                    data_set($responseBody, $resultPath, []);
                    $psrResponse = $response->toPsrResponse();

                    return new Response(new Psr7Response(
                        $psrResponse->getStatusCode(),
                        $psrResponse->getHeaders(),
                        json_encode($responseBody, JSON_THROW_ON_ERROR),
                        $psrResponse->getProtocolVersion(),
                        $psrResponse->getReasonPhrase(),
                    ));
                }

                $userRightsResponse = BaseRepositoryFacade::query()
                    ->when($this->credentials, fn (Builder $query, OnOfficeApiCredentials $credentials) => $query->withCredentials($credentials))
                    ->requestApi(new OnOfficeRequest(
                        actionId: OnOfficeAction::Get,
                        resourceType: OnOfficeResourceType::CheckUserRecordsRight,
                        parameters: [
                            'action' => $action,
                            'module' => $module,
                            'userid' => $userId,
                            'recordIds' => $ids,
                        ],
                    ));

                $allowedIds = $userRightsResponse->json('response.results.0.data.records.0.elements', []);
                $allowedIds = array_map(static fn (string $element): int => (int) $element, $allowedIds);

                /** @var array<int, array{id: string|int}> $records */
                $records = $response->json($resultPath, []);

                $records = array_filter($records, static function (array $record) use ($allowedIds): bool {
                    return in_array((int) $record['id'], $allowedIds, true);
                });

                $responseBody = $response->json();
                data_set($responseBody, $resultPath, array_values($records));
                $psrResponse = $response->toPsrResponse();

                return new Response(new Psr7Response(
                    $psrResponse->getStatusCode(),
                    $psrResponse->getHeaders(),
                    json_encode($responseBody, JSON_THROW_ON_ERROR),
                    $psrResponse->getProtocolVersion(),
                    $psrResponse->getReasonPhrase(),
                ));
            },
            $action,
            $module,
            $userId,
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     *
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
        }, pageSize: $this->pageSize, offset: $this->offset, limit: $this->limit, pageOverwrite: $this->getPageOverwrite());
    }

    /**
     * To have a more flexible way to overwrite the page sizes
     * in stub responses.
     * We need to determine the page size by
     * counting the number of pages in the stub response.
     */
    private function getPageOverwrite(): ?int
    {
        /** @var ?OnOfficeResponse $stub */
        $stub = ($this->stubCallables ?? collect())->first();

        return $stub?->count();
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
        }, $callback, pageSize: $this->pageSize, offset: $this->offset, limit: $this->limit, pageOverwrite: $this->getPageOverwrite());
    }

    /**
     * @throws JsonException
     */
    protected function getStubCallable(OnOfficeRequest $request): ?Response
    {
        // if the request is different from the last one, reset the response cache
        if ($this->requestCache !== $request) {
            $this->requestCache = $request;
            $this->responseCache = ($this->stubCallables ?? collect())->shift();
        }

        /** @var ?OnOfficeResponsePage $response */
        $response = $this->responseCache?->shift();

        if (is_null($response)) {
            return null;
        }

        $response = $response->toResponse();

        $response = new Psr7Response(200, [], json_encode($response, JSON_THROW_ON_ERROR));

        return new Response($response);
    }

    /**
     * @param  array<int, string>|string  $columns
     */
    public function select(array|string $columns = ['ID']): static
    {
        $this->columns = Arr::wrap($columns);

        return $this;
    }

    /**
     * @param  array<int, string>|string  $column
     */
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

    /**
     * @param  string|array<string, mixed>  $column
     */
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

    public function whereNot(string $column, int|string $value): static
    {
        return $this->where($column, '!=', $value);
    }

    /**
     * @param  array<int, mixed>  $values
     */
    public function whereIn(string $column, array $values): static
    {
        return $this->where($column, 'in', $values);
    }

    /**
     * @param  array<int, mixed>  $values
     */
    public function whereNotIn(string $column, array $values): static
    {
        return $this->where($column, 'not in', $values);
    }

    public function whereBetween(string $column, int|string $start, int|string $end): static
    {
        return $this->where($column, 'between', [$start, $end]);
    }

    public function whereLike(string $column, string $value): static
    {
        return $this->where($column, 'like', $value);
    }

    public function whereNotLike(string $column, string $value): static
    {
        return $this->where($column, 'not like', $value);
    }

    /**
     * @return array<string, array<int, array{op: string, val: mixed}>>
     */
    protected function getFilters(): array
    {
        return collect($this->filters)->mapWithKeys(fn (array $value, string $column) => [
            $column => collect($value)->map(function ($filter) {
                [$operator, $value] = $filter;

                return [
                    'op' => $operator,
                    'val' => $value,
                ];
            })->toArray(),
        ])->toArray();
    }

    /**
     * @return array<string, string>
     */
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

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function parameters(array $parameters): static
    {
        $this->customParameters = array_replace_recursive($this->customParameters, $parameters);

        return $this;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     *
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        throw new OnOfficeException('Not implemented');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     *
     * @throws OnOfficeException
     */
    public function call(OnOfficeRequest $request): Collection
    {
        return $this->requestAll($request);
    }

    /**
     * @return array<string, mixed>|null
     *
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
     * @return array<string, mixed>|null
     *
     * @throws OnOfficeException
     */
    public function find(int $id): ?array
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
