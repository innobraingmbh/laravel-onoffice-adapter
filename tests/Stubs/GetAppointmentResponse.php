<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class GetAppointmentResponse
{
    public static function make(array $data = [], int $appointmentId = 100, int $count = 1): PromiseInterface
    {
        return Http::response(self::getBody($data, $appointmentId, $count));
    }

    private static function getBody(array $data, int $appointmentId, int $count): array
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
                        'resourcetype' => 'appointmentList',
                        'cacheable' => true,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => $count,
                            ],
                            'records' => [
                                [
                                    'id' => $appointmentId,
                                    'type' => 'calendar',
                                    'elements' => [
                                        'start_dt' => '2025-01-01 10:00:00',
                                        'end_dt' => '2025-01-01 11:00:00',
                                        'subject' => 'Test Appointment',
                                        'note' => 'Test note',
                                        'type' => 'viewing',
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
