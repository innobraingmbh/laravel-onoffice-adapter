<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class ReadTwoEstateResponse
{
    public static function make(array $data = [], int $estateIdOne = 123, int $estateIdTwo = 124): PromiseInterface
    {
        return Http::response(self::getBody($data, $estateIdOne, $estateIdTwo));
    }

    private static function getBody(array $data, int $estateIdOne, int $estateIdTwo): array
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
                        'resourcetype' => 'estate',
                        'cacheable' => true,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => 2,
                            ],
                            'records' => [
                                [
                                    'id' => $estateIdOne,
                                    'type' => 'estate',
                                    'elements' => [
                                        'Id' => (string) $estateIdOne,
                                        'objekttitel' => 'Nürtingen, Liebermannstr. 6-16',
                                        'kaufpreis' => '0.00',
                                        'kaltmiete' => '0.00',
                                        'vermarktungsart' => 'kauf',
                                        'baujahr' => '1974',
                                        'anzahl_schlafzimmer' => '0.00',
                                        'anzahl_badezimmer' => '0.00',
                                        'strasse' => 'Liebermannstraße 16',
                                        'hausnummer' => '',
                                        'plz' => '72622',
                                        'ort' => 'Nürtingen',
                                        'objektbeschreibung' => '',
                                        'gesamtflaeche' => '0.00',
                                        'wohnflaeche' => '0.00',
                                    ],
                                ],
                                [
                                    'id' => $estateIdTwo,
                                    'type' => 'estate',
                                    'elements' => [
                                        'Id' => (string) $estateIdTwo,
                                        'objekttitel' => 'Nürtingen, Liebermannstr. 6-16',
                                        'kaufpreis' => '0.00',
                                        'kaltmiete' => '0.00',
                                        'vermarktungsart' => 'kauf',
                                        'baujahr' => '1974',
                                        'anzahl_schlafzimmer' => '0.00',
                                        'anzahl_badezimmer' => '0.00',
                                        'strasse' => 'Liebermannstraße 16',
                                        'hausnummer' => '',
                                        'plz' => '72622',
                                        'ort' => 'Nürtingen',
                                        'objektbeschreibung' => '',
                                        'gesamtflaeche' => '0.00',
                                        'wohnflaeche' => '0.00',
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
