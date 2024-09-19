<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class GetRegionsResponse
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
                        'resourcetype' => 'regions',
                        'cacheable' => true,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => null,
                            ],
                            'records' => [
                                [
                                    'id' => 'Verkaufsgebiet C',
                                    'type' => '',
                                    'elements' => [
                                        'id' => 'Verkaufsgebiet C',
                                        'name' => 'Rheinland',
                                        'description' => 'Rheinland',
                                        'postalcodes' => [
                                            [
                                                '52000',
                                                '53000',
                                            ],
                                        ],
                                        'state' => 'NRW',
                                        'country' => 'Deutschland',
                                        'children' => [
                                            'indMulti1274Select5431' => [
                                                'id' => 'indMulti1274Select5431',
                                                'name' => 'Aachen',
                                                'description' => null,
                                                'postalcodes' => [
                                                    [
                                                        '52060',
                                                        '52074',
                                                    ],
                                                ],
                                                'state' => 'Nordrhein-Westfalen',
                                                'country' => 'Deutschland',
                                                'children' => [
                                                ],
                                            ],
                                            'indMulti1274Select5437' => [
                                                'id' => 'indMulti1274Select5437',
                                                'name' => 'Koeln',
                                                'description' => null,
                                                'postalcodes' => [
                                                    [
                                                        '50650',
                                                        '51150',
                                                    ],
                                                ],
                                                'state' => 'Nordrhein-Westfalen',
                                                'country' => 'Deutschland',
                                                'children' => [
                                                    'indMulti1274Select5440' => [
                                                        'id' => 'indMulti1274Select5440',
                                                        'name' => 'Chorweiler',
                                                        'description' => 'Chorweiler ist ein noerdlicher Stadtbezirk von Koeln',
                                                        'postalcodes' => [
                                                            '50765',
                                                        ],
                                                        'state' => 'Nordrhein-Westfalen',
                                                        'country' => 'Deutschland',
                                                        'children' => [
                                                        ],
                                                    ],
                                                    'indMulti1274Select5438' => [
                                                        'id' => 'indMulti1274Select5438',
                                                        'name' => 'Nippes',
                                                        'description' => 'Nippes ist der Stadtbezirk 5 von Koeln',
                                                        'postalcodes' => [
                                                            '50733',
                                                        ],
                                                        'state' => 'Nordrhein-Westfalen',
                                                        'country' => 'Deutschland',
                                                        'children' => [
                                                        ],
                                                    ],
                                                ],
                                            ],
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
