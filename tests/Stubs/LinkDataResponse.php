<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class LinkDataResponse
{
    public static function make(array $data = [], string $tmpUploadId = 'a17ebec0-48f9-44cc-8629-f49ccc68f2d2'): PromiseInterface
    {
        return Http::response(self::getBody($data, $tmpUploadId));
    }

    private static function getBody(array $data, string $tmpUploadId): array
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
                        'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:do',
                        'resourceid' => '',
                        'resourcetype' => 'uploadfile',
                        'cacheable' => false,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => null,
                            ],
                            'records' => [
                                [
                                    'id' => 0,
                                    'type' => '',
                                    'elements' => [
                                        'filesize' => 85,
                                        'tmpUploadId' => $tmpUploadId,
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
