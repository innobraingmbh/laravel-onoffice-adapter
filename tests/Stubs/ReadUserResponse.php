<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class ReadUserResponse
{
    public static function make(array $data = [], int $userId = 19, int $count = 1): PromiseInterface
    {
        return Http::response(self::getBody($data, $userId, $count));
    }

    private static function getBody(array $data, int $userId, int $count): array
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
                        'resourcetype' => 'user',
                        'cacheable' => true,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => $count,
                            ],
                            'records' => [
                                [
                                    'id' => $userId,
                                    'type' => 'user',
                                    'elements' => [
                                        'Vorname' => 'Theo',
                                        'Nachname' => 'Test',
                                        'Emailname' => 'theo test',
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
