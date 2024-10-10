<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests\Stubs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

class GetFiltersResponse
{
    public static function make(array $data = [], int $count = 1): PromiseInterface
    {
        return Http::response(self::getBody($data, $count));
    }

    private static function getBody(array $data, int $count): array
    {
        $records = array_fill(0, $count, [
            'id' => fake()->randomNumber(),
            'type' => 'filter',
            'elements' => [
                'scope' => fake()->randomElement(['estate', 'address']),
                'name' => fake()->words(3, true),
                'userId' => fake()->optional()->randomNumber(),
                'groupId' => fake()->randomNumber(),
            ],
        ]);

        return array_merge_recursive([
            'status' => [
                'code' => 200,
                'errorcode' => 0,
                'message' => 'OK',
            ],
            'response' => [
                'results' => [
                    [
                        'data' => [
                            'meta' => [
                                'cntabsolute' => $count,
                            ],
                            'records' => $records,
                        ],
                    ],
                ],
            ],
        ], $data);
    }
}
