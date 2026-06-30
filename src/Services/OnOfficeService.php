<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
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
                        'timestamp' => Date::now()->timestamp,
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
        return $this->post(
            fn (): array => $request->toRequestArray(),
            fn (Response $response) => $this->throwIfResponseIsFailed($response),
        );
    }

    /**
     * Makes a single request with multiple actions to the onOffice API.
     * All actions are sent in one HTTP call and the API returns
     * one result per action, in the same order.
     *
     * Read more: https://apidoc.onoffice.de/onoffice-api-request/aufbau/
     *
     * @param  array<int, OnOfficeRequest>  $requests
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function requestApiBatch(array $requests): Response
    {
        return $this->post(
            fn (): array => [
                'token' => $this->getToken(),
                'request' => [
                    'actions' => array_map(static fn (OnOfficeRequest $request): array => $request->toActionArray(), $requests),
                ],
            ],
            fn (Response $response) => $this->throwIfBatchResponseIsFailed($response),
        );
    }

    /**
     * Post a freshly built body to the API, retrying on failure.
     *
     * The body is rebuilt on every attempt: each request carries a time-based
     * HMAC, so a retry needs a new timestamp and signature or the API rejects
     * it. That is why the body is a callable rather than a value.
     *
     * @param  callable(): array<string, mixed>  $body
     * @param  callable(Response): void  $throwIfFailed
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    private function post(callable $body, callable $throwIfFailed): Response
    {
        $retryOnlyOnConnectionError = static fn ($exception): bool => $exception instanceof ConnectionException;

        if (! $this->retryOnlyOnConnectionError()) {
            $retryOnlyOnConnectionError = null;
        }

        $response = null;
        retry($this->getRetryCount(), function () use ($body, $throwIfFailed, &$response) {
            $response = Http::withHeaders(config('onoffice.headers'))->post(config('onoffice.base_url'), $body());

            $throwIfFailed($response);
        }, $this->getRetryDelay(), $retryOnlyOnConnectionError);

        /** @var Response $response */
        return $response;
    }

    /**
     * Makes a paginated request to the onOffice API.
     * With a max page calculation based on
     * the total count of records,
     * of the first request.
     *
     * @return Collection<int, array<string, mixed>>
     *
     * @throws OnOfficeException
     */
    public function requestAll(
        callable $request,
        string $resultPath = OnOfficeResponsePath::RECORDS,
        string $countPath = OnOfficeResponsePath::META_COUNT_ABSOLUTE,
        int $pageSize = 500,
        int $offset = 0,
        int $limit = -1,
        ?int $pageOverwrite = null,
    ): Collection {
        $maxPage = $pageOverwrite ?? 0;
        $data = new Collection;
        do {
            try {
                $response = $request($pageSize, $offset);
            } catch (OnOfficeException $exception) {
                Log::error("{$exception->getMessage()} - {$exception->getCode()}");

                throw_if($maxPage === 0 || $pageOverwrite !== null, $exception);

                return $data;
            }

            // If the maxPage is 0,
            // we need to calculate it from the total count of estates
            // and the page size,
            // the first time we get the response from the API
            if ($maxPage === 0) {
                $countAbsolute = $response->json($countPath, 0);
                $maxPage = ceil($countAbsolute / $pageSize);
            }
            $responseResult = $response->json($resultPath);

            if (is_array($responseResult)) {
                $data->push(...$responseResult);
            }

            // if the take parameter is set,
            // and we have more records than the take parameter,
            // we break the loop and return the data except the
            // records that are more than the take parameter
            if ($limit > -1 && $data->count() > $limit) {
                $data = $data->take($limit);
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
     */
    public function requestAllChunked(
        callable $request,
        callable $callback,
        string $resultPath = OnOfficeResponsePath::RECORDS,
        string $countPath = OnOfficeResponsePath::META_COUNT_ABSOLUTE,
        int $pageSize = 500,
        int $offset = 0,
        int $limit = -1,
        ?int $pageOverwrite = null,
    ): void {
        $maxPage = $pageOverwrite ?? 0;
        $elementCount = 0;
        do {
            try {
                $response = $request($pageSize, $offset);
            } catch (OnOfficeException $exception) {
                Log::error("{$exception->getMessage()} - {$exception->getCode()}");

                throw_if($maxPage === 0 || $pageOverwrite !== null, $exception);

                return;
            }

            // If the maxPage is 0,
            // we need to calculate it from the total count of estates
            // and the page size,
            // the first time we get the response from the API
            if ($maxPage === 0) {
                $countAbsolute = $response->json($countPath, 0);

                // if the take parameter is set,
                // and we have more records than the take parameter,
                // we set the countAbsolute to the take parameter
                if ($limit > -1 && $countAbsolute > $limit) {
                    $countAbsolute = $limit;
                }

                $maxPage = ceil($countAbsolute / $pageSize);
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
     * Returns true if the response has a status code greater than 300
     * inside the status dot code key in the response.
     *
     * @throws OnOfficeException
     */
    public function throwIfResponseIsFailed(Response $response): void
    {
        $this->throwIfResultIsFailed($response, 0);
    }

    /**
     * Checks the response status and every result of a
     * multi-action response for errors.
     *
     * @throws OnOfficeException
     */
    public function throwIfBatchResponseIsFailed(Response $response): void
    {
        $resultCount = max(1, count($response->json('response.results', []) ?? []));

        for ($index = 0; $index < $resultCount; $index++) {
            $this->throwIfResultIsFailed($response, $index);
        }
    }

    /**
     * @throws OnOfficeException
     */
    protected function throwIfResultIsFailed(Response $response, int $index): void
    {
        $statusCode = $response->json('status.code', 500);
        $statusErrorCode = $response->json('status.errorcode', 0);
        $responseStatusCode = $response->json(OnOfficeResponsePath::statusErrorCode($index), 0);

        $errorMessage = $response->json('status.message', '');
        if ($errorMessage === '') {
            $errorMessage = "Status code: $statusCode";
        }
        $responseErrorMessage = $response->json(OnOfficeResponsePath::statusMessage($index), '');
        if ($responseErrorMessage === '') {
            $responseErrorMessage = "Status code: $responseStatusCode";
        }

        // Check if the hmac is invalid, due to invalid token or secret
        if ($responseStatusCode === OnOfficeError::The_HMAC_Is_Invalid->value) {
            $token = $this->getToken();
            $secret = $this->getSecret();

            throw_if(strlen($token) !== 32 || strlen($secret) !== 64, OnOfficeException::class, 'The HMAC is invalid. The token must be 32 characters, the secret 64 characters long.', $statusErrorCode, isResponseError: true); // @phpstan-ignore argument.type
        }

        match (true) {
            $statusCode >= 300 && $statusErrorCode > 0 && $responseStatusCode === 0 => throw new OnOfficeException($errorMessage, $statusErrorCode, isResponseError: true, originalResponse: $response),
            $statusCode >= 300 && $statusErrorCode <= 0 && $responseStatusCode === 0 => throw new OnOfficeException($errorMessage, $statusCode, originalResponse: $response),
            $responseStatusCode > 0 => throw new OnOfficeException($responseErrorMessage, $responseStatusCode, isResponseError: true, originalResponse: $response),
            default => null,
        };
    }
}
