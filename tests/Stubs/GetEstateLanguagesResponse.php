<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class GetEstateLanguagesResponse
{
    public static function make(array $data = [], ?int $cntabsolute = null): PromiseInterface
    {
        $body = self::getBody($data);

        if ($cntabsolute !== null) {
            data_set($body, 'response.results.0.data.meta.cntabsolute', $cntabsolute);
        }

        return Http::response($body);
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
                        'resourcetype' => 'estateLanguage',
                        'cacheable' => true,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => 3,
                            ],
                            'records' => [
                                [
                                    'id' => 31,
                                    'type' => 'estateLanguage',
                                    'elements' => [
                                        'language' => 'DEU',
                                        'isMain' => true,
                                        'mainLangId' => 31,
                                    ],
                                ],
                                [
                                    'id' => 51,
                                    'type' => 'estateLanguage',
                                    'elements' => [
                                        'language' => 'ENG',
                                        'isMain' => false,
                                        'mainLangId' => 31,
                                    ],
                                ],
                                [
                                    'id' => 53,
                                    'type' => 'estateLanguage',
                                    'elements' => [
                                        'language' => 'FRA',
                                        'isMain' => false,
                                        'mainLangId' => 31,
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
