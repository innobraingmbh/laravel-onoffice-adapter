<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class ReadAddressResponse
{
    public static function make(array $data = [], int $addressId = 3729, int $count = 1): PromiseInterface
    {
        return Http::response(self::getBody($data, $addressId, $count));
    }

    private static function getBody(array $data, int $addressId, int $count): array
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
                                'cntabsolute' => $count,
                            ],
                            'records' => [
                                [
                                    'id' => $addressId,
                                    'type' => 'address',
                                    'elements' => [
                                        'id' => (string) $addressId,
                                        'Briefanrede' => 'Herr',
                                        'Vorname' => 'Max',
                                        'Name' => 'Mustermann',
                                        'Land' => 'Deutschland',
                                        'Ort' => 'Musterstadt',
                                        'Plz' => '12345',
                                        'Strasse' => 'Musterstraße',
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
