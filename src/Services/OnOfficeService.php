<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeApiCredentials;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeError;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Throwable;

class OnOfficeService
{
    use OnOfficeDefaultFieldConst;
    use OnOfficeParameterConst;

    public function __construct(
        private ?OnOfficeApiCredentials $credentials = null
    ) {}

    public function setCredentials(?OnOfficeApiCredentials $credentials): static
    {
        $this->credentials = $credentials;

        return $this;
    }

    public function getToken(): string
    {
        if ($this->credentials instanceof OnOfficeApiCredentials) {
            return $this->credentials->token;
        }

        return Config::get('onoffice.token', '') ?? '';
    }

    public function getSecret(): string
    {
        if ($this->credentials instanceof OnOfficeApiCredentials) {
            return $this->credentials->secret;
        }

        return Config::get('onoffice.secret', '') ?? '';
    }

    public function getApiClaim(): string
    {
        if ($this->credentials instanceof OnOfficeApiCredentials) {
            return $this->credentials->apiClaim;
        }

        return Config::get('onoffice.api_claim', '') ?? '';
    }

    public function getRetryCount(): int
    {
        $count = Config::get('onoffice.retry.count', 3) ?? 3;

        return max($count, 1);
    }

    public function getRetryDelay(): int
    {
        $delay = Config::get('onoffice.retry.delay', 200) ?? 200;

        return max($delay, 1);
    }

    public function retryOnlyOnConnectionError(): bool
    {
        return Config::get('onoffice.retry.only_on_connection_error', true) ?? true;
    }

    /*
     * Generates a HMAC for the onOffice API request.
     * The new HMAC is calculated by concatenating the values of the parameters
     * timestamp, token, resourcetype and actionid in this order.
     * A SHA256 hash is formed from this string (with the secret as the key)
     * and the resulting binary string must then be base64 encoded.
     *
     * Read more: https://apidoc.onoffice.de/onoffice-api-request/request-elemente/action/#hmac
     */
    public function getHmac(OnOfficeAction $actionId, OnOfficeResourceType|string $resourceType): string
    {
        return base64_encode(
            hash_hmac(
                'sha256',
                implode(
                    '',
                    [
                        'timestamp' => Carbon::now()->timestamp,
                        'token' => $this->getToken(),
                        'resourcetype' => $resourceType instanceof OnOfficeResourceType
                            ? $resourceType->value
                            : $resourceType,
                        'actionid' => $actionId->value,
                    ]
                ),
                $this->getSecret(),
                true
            )
        );
    }

    /**
     * Makes a request to the onOffice API.
     * Throws an exception if the request fails.
     *
     * Read more: https://apidoc.onoffice.de/onoffice-api-request/aufbau/
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function requestApi(OnOfficeRequest $request): Response
    {
        $retryOnlyOnConnectionError = static fn ($exception): bool => $exception instanceof ConnectionException;

        if (! $this->retryOnlyOnConnectionError()) {
            $retryOnlyOnConnectionError = null;
        }

        /*
         * All requests have a time-based validation.
         * If we retry the request, the timestamp will be different.
         * In this case, the HMAC will be invalid.
         * To avoid this, we need to retry the request with payload creation until we get a valid response.
         */
        $response = null;
        retry($this->getRetryCount(), function () use ($request, &$response) {
            $response = Http::withHeaders(config('onoffice.headers'))->post(config('onoffice.base_url'), $request->toRequestArray());

            $this->throwIfResponseIsFailed($response);
        }, $this->getRetryDelay(), $retryOnlyOnConnectionError);

        return $response;
    }

    /**
     * Makes a paginated request to the onOffice API.
     * With a max page calculation based on
     * the total count of records,
     * of the first request.
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function requestAll(
        callable $request,
        string $resultPath = 'response.results.0.data.records',
        string $countPath = 'response.results.0.data.meta.cntabsolute',
        int $pageSize = 500,
        int $offset = 0,
        int $limit = -1,
        ?int $pageOverwrite = null,
    ): Collection {
        $maxPage = $pageOverwrite ?? 0;
        $data = new Collection;

        do {
            $response = $this->tryCallable($request, $pageSize, $offset, $maxPage, $pageOverwrite);

            if (! $response instanceof Response) {
                return $data;
            }

            // If the maxPage is 0,
            // we need to calculate it from the total count of estates
            // and the page size,
            // the first time we get the response from the API
            if ($maxPage === 0) {
                $maxPage = $this->getMaxPage($response, $countPath, $pageSize, $limit);
            }

            $responseResult = $response->json($resultPath);

            if (is_array($responseResult)) {
                $data->push(...$responseResult);
            }

            // if the take parameter is set,
            // and we have more records than the take parameter,
            // we break the loop and return the data except the
            // records that are more than the take parameter
            if (($limitedData = $this->reachedLimit($limit, $data)) instanceof Collection) {
                $data = $limitedData;
                break;
            }

            $offset += $pageSize;
            $currentPage = $offset / $pageSize;
        } while ($maxPage > $currentPage);

        return $data;
    }

    /**
     * Makes a paginated request to the onOffice API.
     * With a max page calculation based on
     * the total count of records,
     * of the first request.
     *
     * The request will not return a collection containing the records,
     * but will call the given callback function with the records of each page.
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function requestAllChunked(
        callable $request,
        callable $callback,
        string $resultPath = 'response.results.0.data.records',
        string $countPath = 'response.results.0.data.meta.cntabsolute',
        int $pageSize = 500,
        int $offset = 0,
        int $limit = -1,
        ?int $pageOverwrite = null,
    ): void {
        $maxPage = $pageOverwrite ?? 0;
        $elementCount = 0;
        do {
            $response = $this->tryCallable($request, $pageSize, $offset, $maxPage, $pageOverwrite);

            if (! $response instanceof Response) {
                return;
            }

            // If the maxPage is 0,
            // we need to calculate it from the total count of estates
            // and the page size,
            // the first time we get the response from the API
            if ($maxPage === 0) {
                $maxPage = $this->getMaxPage($response, $countPath, $pageSize, $limit);
            }

            // If the take parameter is set,
            // and we have more records than the take parameter.
            // We break the loop and return the sliced records
            // because it is not guaranteed that the record page size
            // will be the same as the take parameter
            $elements = $response->json($resultPath);
            $elementCount += count($elements ?? []);
            if ($limit > -1 && $elementCount > $limit) {
                $elements = array_slice($elements, 0, $limit - $elementCount);
            }

            $callback($elements);

            $offset += $pageSize;
            $currentPage = $offset / $pageSize;
        } while ($maxPage > $currentPage);
    }

    /**
     * Makes a paginated request to the onOffice API.
     * With a max page calculation based on
     * the total count of records,
     * of the first request.
     * All requests, but the first one,
     * will be executed concurrently.
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function requestConcurrently(
        callable $request,
        string $resultPath = 'response.results.0.data.records',
        string $countPath = 'response.results.0.data.meta.cntabsolute',
        int $pageSize = 500,
        int $offset = 0,
        int $limit = -1,
        ?int $pageOverwrite = null,
    ): Collection {
        $maxPage = $pageOverwrite ?? 0;
        $data = new Collection;

        $response = $this->tryCallable($request, $pageSize, $offset, $maxPage, $pageOverwrite);

        if (! $response instanceof Response) {
            return $data;
        }

        // If the maxPage is 0,
        // we need to calculate it from the total count of estates
        // and the page size,
        // the first time we get the response from the API
        if ($maxPage === 0) {
            $maxPage = $this->getMaxPage($response, $countPath, $pageSize, $limit);
        }
        $responseResult = $response->json($resultPath);

        if (is_array($responseResult)) {
            $data->push(...$responseResult);
        }

        if (($limitedData = $this->reachedLimit($limit, $data)) instanceof Collection) {
            return $limitedData;
        }

        $offset += $pageSize;
        $currentPage = $offset / $pageSize;

        $requests = [];

        while ($maxPage > $currentPage) {
            $requests[] = fn () => $request($pageSize, $offset);

            $offset += $pageSize;
            $currentPage = $offset / $pageSize;
        }

        /*
         * Using default driver (process)
         * fork is not recommended here,
         * because we want to have the Repositories available
         * on request level.
         * Fork does not work inside the request cycle
         */
        $responses = Concurrency::run($requests);

        collect($responses)->each(function (Response $response) use ($resultPath, &$data) {
            $responseResult = $response->json($resultPath);

            if (is_array($responseResult)) {
                $data->push(...$responseResult);
            }
        });

        if (($limitedData = $this->reachedLimit($limit, $data)) instanceof Collection) {
            return $limitedData;
        }

        return $data;
    }

    /**
     * Returns true if the response has a status code greater than 300
     * inside the status dot code key in the response.
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function throwIfResponseIsFailed(Response $response): void
    {
        $statusCode = $response->json('status.code', 500);
        $statusErrorCode = $response->json('status.errorcode', 0);
        $responseStatusCode = $response->json('response.results.0.status.errorcode', 0);

        $errorMessage = $response->json('status.message', '');
        if ($errorMessage === '') {
            $errorMessage = "Status code: $statusCode";
        }
        $responseErrorMessage = $response->json('response.results.0.status.message', '');
        if ($responseErrorMessage === '') {
            $responseErrorMessage = "Status code: $responseStatusCode";
        }

        // Check if the hmac is invalid, due to invalid token or secret
        if ($responseStatusCode === OnOfficeError::The_HMAC_Is_Invalid->value) {
            $token = $this->getToken();
            $secret = $this->getSecret();

            throw_if(strlen($token) !== 32 || strlen($secret) !== 64, new OnOfficeException('The HMAC is invalid. The token must be 32 characters, the secret 64 characters long.', $statusErrorCode, isResponseError: true));
        }

        match (true) {
            $statusCode >= 300 && $statusErrorCode > 0 && $responseStatusCode === 0 => throw new OnOfficeException($errorMessage, $statusErrorCode, isResponseError: true, originalResponse: $response),
            $statusCode >= 300 && $statusErrorCode <= 0 && $responseStatusCode === 0 => throw new OnOfficeException($errorMessage, $statusCode, originalResponse: $response),
            $responseStatusCode > 0 => throw new OnOfficeException($responseErrorMessage, $responseStatusCode, isResponseError: true, originalResponse: $response),
            default => null,
        };
    }

    /**
     * If the take parameter is set,
     * and we have more records than the take parameter,
     * we return a collection with the sliced records.
     * Otherwise, we return null.
     */
    protected function reachedLimit(int $limit, Collection $data): ?Collection
    {
        if ($limit > -1 && $data->count() >= $limit) {
            return $data->take($limit);
        }

        return null;
    }

    /**
     * @throws Throwable
     */
    protected function tryCallable(callable $request, int $pageSize, int $offset, int $maxPage, ?int $pageOverwrite): ?Response
    {
        try {
            return $request($pageSize, $offset);
        } catch (OnOfficeException $exception) {
            Log::error("{$exception->getMessage()} - {$exception->getCode()}");

            throw_if($maxPage === 0 || $pageOverwrite !== null, $exception);
        }

        return null;
    }

    /**
     * If the maxPage is 0,
     * we need to calculate it from the total count of records
     * and the page size,
     * the first time we get the response from the API.
     */
    protected function getMaxPage(Response $response, string $countPath, int $pageSize, int $limit): int
    {
        $countAbsolute = $response->json($countPath, 0);

        if ($limit > -1 && $countAbsolute > $limit) {
            $countAbsolute = $limit;
        }

        return (int) ceil($countAbsolute / $pageSize);
    }
}
