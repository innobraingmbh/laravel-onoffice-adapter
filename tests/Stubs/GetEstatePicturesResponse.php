<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class GetEstatePicturesResponse
{
    public static function make(array $data = [], int $count = 2): PromiseInterface
    {
        return Http::response(self::getBody($data, $count));
    }

    private static function getBody(array $data, int $count): array
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
                        'resourceid' => 'estate',
                        'resourcetype' => 'file',
                        'cacheable' => true,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => $count,
                            ],
                            'records' => [
                                [
                                    'id' => 1281,
                                    'type' => 'file',
                                    'elements' => [
                                        'type' => 'Foto',
                                        'position' => 1,
                                        'documentAttribute' => 'document_expose',
                                        'name' => '6967a002-62f2-48cf-b7e4-2b99e73b6316',
                                        'originalname' => 'DSC08721.jpg',
                                        'title' => 'DSC08721',
                                        'freetext' => 'hi miyu',
                                        'modified' => 1702037103,
                                        'category' => 'external',
                                    ],
                                ],
                                [
                                    'id' => 1283,
                                    'type' => 'file',
                                    'elements' => [
                                        'type' => 'Titelbild',
                                        'position' => 2,
                                        'documentAttribute' => '',
                                        'name' => 'de8f6655-907b-4fc3-a87e-0e7fd8792041',
                                        'originalname' => 'DSC08721-2.jpg',
                                        'title' => 'DSC08721-2',
                                        'freetext' => 'dsfafadf',
                                        'modified' => 1702037103,
                                        'category' => 'external',
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
        ], $data);
    }
}
