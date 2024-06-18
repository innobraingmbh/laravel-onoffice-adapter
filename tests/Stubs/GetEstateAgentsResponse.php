<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class GetEstateAgentsResponse
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
                        'resourcetype' => 'idsfromrelation',
                        'cacheable' => true,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => null,
                            ],
                            'records' => [
                                [
                                    'id' => 'relatedIds',
                                    'type' => '',
                                    'elements' => [
                                        5779 => [
                                            '2169',
                                            '2205',
                                        ],
                                        5781 => [
                                            '2169',
                                            '2205',
                                        ],
                                        5783 => [
                                            '2169',
                                            '2205',
                                        ],
                                        5785 => [
                                            '2169',
                                            '2205',
                                        ],
                                        5789 => [
                                            '2169',
                                        ],
                                        5791 => [
                                            '2169',
                                        ],
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
