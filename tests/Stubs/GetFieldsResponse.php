<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class GetFieldsResponse
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
                        'resourcetype' => 'fields',
                        'cacheable' => true,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => null,
                            ],
                            'records' => [
                                [],
                                [],
                                [],
                                [],
                                [],
                                [],
                                [],
                                [],
                                [],
                                [],
                                [],
                                [],
                                [
                                    'id' => 'estate',
                                    'type' => '',
                                    'elements' => [
                                        'objektart' => [
                                            'type' => 'singleselect',
                                            'length' => null,
                                            'permittedvalues' => [
                                                'zimmer',
                                                'haus',
                                                'wohnung',
                                                'grundstueck',
                                                'buero_praxen',
                                                'einzelhandel',
                                                'gastgewerbe',
                                                'hallen_lager_prod',
                                                'land_und_forstwirtschaft',
                                                'freizeitimmbilien_gewerblich',
                                                'sonstige',
                                                'hausbau',
                                            ],
                                            'default' => null,
                                            'filters' => [
                                            ],
                                            'dependencies' => [
                                            ],
                                            'compoundFields' => [
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
