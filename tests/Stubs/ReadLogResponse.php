<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class ReadLogResponse
{
    public static function make(array $data = [], int $count = 1): PromiseInterface
    {
        return Http::response(self::getBody($data, $count));
    }

    private static function getBody(array $data, int $count): array
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
                        'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
                        'resourceid' => '',
                        'resourcetype' => 'log',
                        'cacheable' => false,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => $count,
                            ],
                            'records' => [
                                [
                                    'id' => 126913,
                                    'type' => 'Log',
                                    'elements' => [
                                        'id' => '126913',
                                        'action' => 'edit',
                                        'module' => 'user',
                                        'userId' => null,
                                        'dateTime' => '2021-09-29 14:00:00',
                                        'resourceId' => '19',
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
