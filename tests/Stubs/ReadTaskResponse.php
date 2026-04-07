<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class ReadTaskResponse
{
    public static function make(array $data = [], int $taskId = 1, int $count = 1): PromiseInterface
    {
        return Http::response(self::getBody($data, $taskId, $count));
    }

    private static function getBody(array $data, int $taskId, int $count): array
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
                        'resourcetype' => 'task',
                        'cacheable' => false,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => $count,
                            ],
                            'records' => [
                                [
                                    'id' => $taskId,
                                    'type' => 'task',
                                    'elements' => [
                                        'subject' => 'Test Task',
                                        'status' => 'open',
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
