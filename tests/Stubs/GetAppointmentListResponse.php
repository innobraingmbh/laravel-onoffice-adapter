<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class GetAppointmentListResponse
{
    public static function make(array $data = [], int $appointmentId = 1, int $count = 1): PromiseInterface
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
                        'cacheable' => false,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => $count,
                            ],
                            'records' => [
                                [
                                    'id' => $appointmentId,
                                    'type' => 'appointmentList',
                                    'elements' => [
                                        'subject' => 'Test Appointment',
                                        'date' => '2026-03-25 10:00:00',
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
