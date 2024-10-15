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
                        'resourceid' => '',
                        'resourcetype' => 'estatepictures',
                        'cacheable' => true,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => 5,
                            ],
                            'records' => [
                                [
                                    'id' => 1293,
                                    'type' => 'files',
                                    'elements' => [
                                        [
                                            'estateid' => '5791',
                                            'type' => 'Titelbild',
                                            'url' => 'https://image.onoffice.de/smart20/Objekte/innobrain-dev/5791/091d88cf-33af-4445-8535-c911ab661c08.jpg',
                                            'title' => 'b7cfe256-0b12-42bd-8d8d-1832f8d9dcb8-1704059250',
                                            'text' => '',
                                            'originalname' => 'b7cfe256-0b12-42bd-8d8d-1832f8d9dcb8-1704059250.jpeg',
                                            'modified' => 1713287522,
                                            'estateMainId' => '5791',
                                        ],
                                    ],
                                ],
                                [
                                    'id' => 1295,
                                    'type' => 'files',
                                    'elements' => [
                                        [
                                            'estateid' => '5791',
                                            'type' => 'Foto',
                                            'url' => 'https://image.onoffice.de/smart20/Objekte/innobrain-dev/5791/ef6eb49f-a048-4c12-9c03-2f15f03fc39e.jpg',
                                            'title' => 'b5eef46c-43a0-424d-acea-786a9b8c1cdb-1704059254',
                                            'text' => '',
                                            'originalname' => 'b5eef46c-43a0-424d-acea-786a9b8c1cdb-1704059254.jpeg',
                                            'modified' => 1713287523,
                                            'estateMainId' => '5791',
                                        ],
                                    ],
                                ],
                                [
                                    'id' => 1297,
                                    'type' => 'files',
                                    'elements' => [
                                        [
                                            'estateid' => '5791',
                                            'type' => 'Foto',
                                            'url' => 'https://image.onoffice.de/smart20/Objekte/innobrain-dev/5791/3c5d1ad3-b84b-40e8-ac0f-d58b7666fd0d.jpg',
                                            'title' => '036f3a2e-10fe-415c-9911-72d459d7cb82-1704059278',
                                            'text' => '',
                                            'originalname' => '036f3a2e-10fe-415c-9911-72d459d7cb82-1704059278.jpeg',
                                            'modified' => 1713287523,
                                            'estateMainId' => '5791',
                                        ],
                                    ],
                                ],
                                [
                                    'id' => 1299,
                                    'type' => 'files',
                                    'elements' => [
                                        [
                                            'estateid' => '5791',
                                            'type' => 'Foto',
                                            'url' => 'https://image.onoffice.de/smart20/Objekte/innobrain-dev/5791/3fc5bcc5-5b01-4f3b-ac91-85aa2660809e.jpg',
                                            'title' => 'ba419831-4517-467e-abd8-6f9820fa95f9-1704059281',
                                            'text' => '',
                                            'originalname' => 'ba419831-4517-467e-abd8-6f9820fa95f9-1704059281.jpeg',
                                            'modified' => 1713287523,
                                            'estateMainId' => '5791',
                                        ],
                                    ],
                                ],
                                [
                                    'id' => 1305,
                                    'type' => 'files',
                                    'elements' => [
                                        [
                                            'estateid' => '5791',
                                            'type' => 'Foto',
                                            'url' => 'https://image.onoffice.de/smart20/Objekte/innobrain-dev/5791/2c16e61a-e24b-4ccf-a7d7-3552f32308c3.jpg',
                                            'title' => '1c6333c1-ac10-46ae-b2aa-494e5d4a5d64-1704059316',
                                            'text' => '',
                                            'originalname' => '1c6333c1-ac10-46ae-b2aa-494e5d4a5d64-1704059316.jpeg',
                                            'modified' => 1713957519,
                                            'estateMainId' => '5791',
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
        ], $data);
    }
}
