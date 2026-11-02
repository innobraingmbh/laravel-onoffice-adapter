<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class GetAddressHierarchyContactsResponse
{
    public static function make(): PromiseInterface
    {
        return Http::response([
            'status' => [
                'code' => 200,
                'errorcode' => 0,
                'message' => 'OK',
            ],
            'response' => [
                'results' => [
                    [
                        'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:get',
                        'resourceid' => '691',
                        'resourcetype' => 'addressHierarchyContacts',
                        'cacheable' => false,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => null,
                            ],
                            'records' => [
                                [
                                    'id' => 691,
                                    'type' => 'address',
                                    'elements' => [
                                        'id' => 691,
                                        'customerno' => '599',
                                        'firstname' => '',
                                        'surname' => '',
                                        'company' => 'Mufflon GmbH',
                                        'primaryEmail' => 'info@my-onoffice.com',
                                    ],
                                ],
                                [
                                    'id' => 693,
                                    'type' => 'address',
                                    'elements' => [
                                        'id' => 693,
                                        'customerno' => '600',
                                        'firstname' => 'Geralinde',
                                        'surname' => 'Mufflon',
                                        'company' => 'Mufflon GmbH',
                                        'primaryEmail' => 'g.mufflon@my-onoffice.com',
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
        ]);
    }
}
