<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\ActivityRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadActivityResponse;

describe('fake responses', function () {
    test('get', function () {
        ActivityRepository::fake(ActivityRepository::response([
            ActivityRepository::page(recordFactories: [
                AddressFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = ActivityRepository::query()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        ActivityRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                ReadActivityResponse::make(count: 1500),
                ReadActivityResponse::make(count: 1500),
                ReadActivityResponse::make(count: 1500),
            ]),
        ]);

        ActivityRepository::record();

        $response = ActivityRepository::query()->get();

        expect($response->count())->toBe(3);

        ActivityRepository::assertSentCount(3);
    });
});
