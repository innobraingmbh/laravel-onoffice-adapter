<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class InvalidHmacResponse
{
    public static function make(array $data = []): PromiseInterface
    {
        return Http::response(self::getBody($data));
    }

    private static function getBody(array $data): array
    {
        return array_merge_recursive([
            'status' => [
                'code' => 200,
                'errorcode' => 0,
                'message' => 'OK',
            ],
            'response' => [
                'results' => [
                    [
                        'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:get',
                        'resourceid' => '',
                        'resourcetype' => 'actionkindtypes',
                        'cacheable' => false,
                        'identifier' => '',
                        'data' => [],
                        'status' => [
                            'errorcode' => 137,
                            'message' => 'The HMAC is invalid',
                        ],
                    ],
                ],
            ],
        ], $data);
    }
}
