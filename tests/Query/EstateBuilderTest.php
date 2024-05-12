<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\EstateRepository;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadEstateResponse;

it('works', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*' => Http::sequence([
            // Each response will have 600 estates to simulate pagination
            ReadEstateResponse::make(estateId: 1, count: 1500),
            ReadEstateResponse::make(estateId: 2, count: 1500),
            ReadEstateResponse::make(estateId: 3, count: 1500),
        ]),
    ]);

    $estates = EstateRepository::query()
        ->get();

    expect($estates)
        ->toHaveCount(3)
        ->and($estates->first()['id'])->toBe(1)
        ->and($estates->last()['id'])->toBe(3);
});

it('works with difficult request', function () {
    Http::preventStrayRequests();
    Http::fake();

    EstateRepository::query()
        ->select('Id')
        ->where('status', 1)
        ->where('kaufpreis', '<', 30_000)
        ->orderBy('kaufpreis')
        ->orderBy('warmmiete')
        ->get();

    Http::assertSent(/**
     * @throws JsonException
     */ static function (Request $request) {
        $body = json_decode($request->body(), true, 512, JSON_THROW_ON_ERROR);
        $actual = data_get($body, 'request.actions.0.parameters');

        expect($actual)
            ->toBeArray()
            ->and(data_get($actual, 'data.0'))->toBe('Id')
            ->and(data_get($actual, 'filter'))->toBe([
                'status' => [
                    'op' => '=',
                    'val' => 1,
                ],
                'kaufpreis' => [
                    'op' => '<',
                    'val' => 30_000,
                ],
            ])
            ->and(data_get($actual, 'sortby'))->toBe([
                'kaufpreis' => 'ASC',
                'warmmiete' => 'ASC',
            ])
            ->and(data_get($actual, 'listlimit'))->toBe(500)
            ->and(data_get($actual, 'listoffset'))->toBe(0);

        return true;
    });
});
