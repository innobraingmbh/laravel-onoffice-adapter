<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class ReadActivityResponse
{
    public static function make(array $data = [], int $activityId = 67075, int $count = 1): PromiseInterface
    {
        return Http::response(self::getBody($data, $activityId, $count));
    }

    private static function getBody(array $data, int $activityId, int $count): array
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
                        'resourcetype' => 'agentslog',
                        'cacheable' => true,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => $count,
                            ],
                            'records' => [
                                [
                                    'id' => $activityId,
                                    'type' => 'address',
                                    'elements' => [
                                        'Objekt_nr' => [
                                            '2529',
                                        ],
                                        'Aktionsart' => 'Email',
                                        'Aktionstyp' => 'Ausgang',
                                        'Datum' => '2021-02-23 12:28:30',
                                        'Bemerkung' => 'Vertragsunterlage',
                                        'merkmal' => null,
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
