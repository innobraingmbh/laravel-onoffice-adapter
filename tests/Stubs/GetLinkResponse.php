<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class GetLinkResponse
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
                        'resourceid' => 'estate',
                        'resourcetype' => 'getlink',
                        'cacheable' => false,
                        'identifier' => '',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => null,
                            ],
                            'records' => [
                                [
                                    'id' => 5979,
                                    'type' => '',
                                    'elements' => [
                                        'url' => 'https://beta.smart.onoffice.de/smart/smart.php?params=5nQt5%2B3vEm8pMspgWpqAdCzylyROxhpTIzFucBqtNpuysiqbyYnSblYFrB%2BCq8NWzDxGjsYc%2FmhPFpGh0fnvaJzJlQSSvDNWADJ%2BofMt6hQKAiBDyrb9fVEJkIJKtIqS%2FFVclqS%2FqUO0vUBsuY7lsw%3D%3D'
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
