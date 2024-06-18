<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class DoUnlockProviderResponse
{
    public static function make(array $data = [], bool $success = true): PromiseInterface
    {
        return Http::response(self::getBody($data, $success));
    }

    private static function getBody(array $data, bool $success): array
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
                        'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:do',
                        'resourceid' => '',
                        'resourcetype' => 'unlockProvider',
                        'cacheable' => false,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => null,
                            ],
                            'records' => [
                                [
                                    'id' => 0,
                                    'type' => '',
                                    'elements' => [
                                        'success' => $success ? 'success' : 'error',
                                    ],
                                ],
                            ],
                            'status' => [
                                'errorcode' => 0,
                                'message' => 'OK',
                            ],
                        ],
                    ],
                ],
            ],
        ], $data);
    }
}
