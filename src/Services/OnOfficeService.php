<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeError;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Throwable;

class OnOfficeService
{
    use OnOfficeParameterConst;

    public function __construct() {}

    public function getToken(): string
    {
        return Config::get('onoffice.token', '') ?? '';
    }

    public function getSecret(): string
    {
        return Config::get('onoffice.secret', '') ?? '';
    }

    public function getApiClaim(): string
    {
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
    private function getHmac(OnOfficeAction $actionId, OnOfficeResourceType $resourceType): string
    {
        return base64_encode(
            hash_hmac(
                'sha256',
                implode(
                    '',
                    [
                        'timestamp' => Carbon::now()->timestamp,
                        'token' => $this->getToken(),
                        'resourcetype' => $resourceType->value,
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
    public function requestApi(
        OnOfficeAction $actionId,
        OnOfficeResourceType $resourceType,
        OnOfficeResourceId|string|int $resourceId = OnOfficeResourceId::None,
        string|int $identifier = '',
        array $parameters = [],
    ): Response {
        if (! empty($this->getApiClaim())) {
            $parameters = array_replace([self::EXTENDEDCLAIM => $this->getApiClaim()], $parameters);
        }

        $retryOnlyOnConnectionError = static function ($exception): bool {
            return $exception instanceof ConnectionException;
        };

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
        retry($this->getRetryCount(), function () use ($parameters, $identifier, $resourceType, $resourceId, $actionId, &$response) {
            $response = Http::onOffice()
                ->post('/', [
                    'token' => $this->getToken(),
                    'request' => [
                        'actions' => [
                            [
                                'actionid' => $actionId->value,
                                'resourceid' => $resourceId instanceof OnOfficeResourceId ? $resourceId->value : $resourceId,
                                'resourcetype' => $resourceType->value,
                                'identifier' => $identifier,
                                'timestamp' => Carbon::now()->timestamp,
                                'hmac' => $this->getHmac($actionId, $resourceType),
                                'hmac_version' => 2,
                                'parameters' => $parameters,
                            ],
                        ],
                    ],
                ]);

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
            try {
                $response = $request($pageSize, $offset);
            } catch (OnOfficeException $exception) {
                Log::error("{$exception->getMessage()} - {$exception->getCode()}");

                if ($maxPage === 0 || $pageOverwrite !== null) {
                    throw $exception;
                }

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
            try {
                $response = $request($pageSize, $offset);
            } catch (OnOfficeException $exception) {
                Log::error("{$exception->getMessage()} - {$exception->getCode()}");

                if ($maxPage === 0 || $pageOverwrite !== null) {
                    throw $exception;
                }

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

            if (strlen($token) !== 32 || strlen($secret) !== 64) {
                throw new OnOfficeException('The HMAC is invalid. The token must be 32 characters, the secret 64 characters long.', $statusErrorCode, isResponseError: true);
            }
        }

        match (true) {
            $statusCode >= 300 && $statusErrorCode > 0 => throw new OnOfficeException($errorMessage, $statusErrorCode, isResponseError: true),
            $statusCode >= 300 && $statusErrorCode <= 0 => throw new OnOfficeException($errorMessage, $statusCode),
            $responseStatusCode > 0 => throw new OnOfficeException($responseErrorMessage, $responseStatusCode, isResponseError: true),
            default => null,
        };
    }
}
