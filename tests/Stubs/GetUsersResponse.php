<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class GetUsersResponse
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
                        'resourceid' => '',
                        'resourcetype' => 'users',
                        'cacheable' => true,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => null,
                            ],
                            'records' => [
                                [
                                    'id' => 25,
                                    'type' => null,
                                    'elements' => [
                                        'id' => 25,
                                        'username' => 'paulprobe',
                                        'firstname' => 'Paul',
                                        'lastname' => 'Probe',
                                        'email' => 'paulprobe154@onoffice.de',
                                        'meetingurl' => 'www.meetinglink3178.com',
                                        'appointmentColor' => 'FFFF00',
                                        'code' => 'PP',
                                        'online' => 'online',
                                    ],
                                ],
                                [
                                    'id' => 17,
                                    'type' => null,
                                    'elements' => [
                                        'id' => 17,
                                        'username' => 'theotest',
                                        'firstname' => 'Theo',
                                        'lastname' => 'Test',
                                        'email' => 'theotest154@onoffice.de',
                                        'meetingurl' => 'www.meetinglink6542.com',
                                        'appointmentColor' => 'B04F00',
                                        'code' => 'TT',
                                        'online' => 'offline',
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
