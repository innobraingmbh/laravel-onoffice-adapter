<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class GetEstateAgentsDetailedResponse
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
                        'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
                        'resourceid' => '',
                        'resourcetype' => 'address',
                        'cacheable' => true,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => null,
                            ],
                            'records' => [
                                [
                                    'id' => 2169,
                                    'type' => 'address',
                                    'elements' => [
                                        'id' => 2169,
                                        'Vorname' => 'Foo',
                                        'Name' => 'Bar',
                                        'imageUrl' => '',
                                        'emailbusiness__3757' => 'foo@bar.de',
                                    ],
                                ],
                                [
                                    'id' => 2205,
                                    'type' => 'address',
                                    'elements' => [
                                        'id' => 2205,
                                        'Vorname' => 'Bar',
                                        'Name' => 'Foo',
                                        'imageUrl' => '',
                                        'email__3811' => 'bar@bar.de',
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
