<?php

namespace Katalam\OnOfficeAdapter\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;

class OnOfficeService
{
    /*
     * Parameter constants for the onOffice API request.
     */
    public const DATA = 'data';

    public const LISTLIMIT = 'listlimit';

    public const LISTOFFSET = 'listoffset';

    private string $token;

    private string $secret;

    public function __construct()
    {
        $this->token = config('onoffice.token');
        $this->secret = config('onoffice.secret');
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getSecret(): string
    {
        return $this->secret;
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
                        'token' => $this->token,
                        'resourcetype' => $resourceType->value,
                        'actionid' => $actionId->value,
                    ]
                ),
                $this->secret,
                true
            )
        );
    }

    /*
     * Makes a request to the onOffice API.
     * Throws an exception if the request fails.
     *
     * Read more: https://apidoc.onoffice.de/onoffice-api-request/aufbau/
     */
    /**
     * @throws OnOfficeException
     */
    public function requestApi(
        OnOfficeAction $actionId,
        OnOfficeResourceType $resourceType,
        OnOfficeResourceId $resourceId = OnOfficeResourceId::None,
        string|int $identifier = '',
        array $parameters = [],
    ): Response {
        $response = Http::onOffice()
            ->post('/', [
                'token' => $this->token,
                'request' => [
                    'actions' => [
                        [
                            'actionid' => $actionId->value,
                            'resourceid' => $resourceId->value,
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

        if ($response->json('status.code') !== 200) {
            throw new OnOfficeException('Failed to request OnOffice API');
        }

        return $response;
    }

    /**
     * Makes a paginated request to the onOffice API.
     * With a max page calculation based on
     * the total count of records,
     * of the first request.
     */
    public function requestAll(
        callable $request,
        string $resultPath = 'response.results.0.data.records',
        string $countPath = 'response.results.0.data.meta.cntabsolute',
        int $pageSize = 200,
        int $offset = 0
    ): Collection {
        $maxPage = 0;
        $data = collect();
        do {
            $response = $request($pageSize, $offset);

            if ($response->json('status.code') !== 200) {
                Log::error('Failed to request estates from onOffice API');

                return $data;
            }

            // If the maxPage is 0,
            // we need to calculate it from the total count of estates
            // and the page size,
            // the first time we get the response from the API
            if ($maxPage === 0) {
                $countAbsolute = $response->json($countPath);
                $maxPage = ceil($countAbsolute / $pageSize);
            }

            $data->push(...$response->json($resultPath));

            $offset += $pageSize;
            $currentPage = $offset / $pageSize;
        } while ($maxPage > $currentPage);

        return $data;
    }
}
