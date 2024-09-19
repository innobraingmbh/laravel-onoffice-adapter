<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\ReadEstateResponse;

describe('fake responses', function () {
    test('get', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(recordFactories: [
                EstateFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = EstateRepository::query()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        EstateRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                ReadEstateResponse::make(count: 1500),
                ReadEstateResponse::make(count: 1500),
                ReadEstateResponse::make(count: 1500),
            ]),
        ]);

        EstateRepository::record();

        $response = EstateRepository::query()->get();

        expect($response->count())->toBe(3);

        EstateRepository::assertSentCount(3);
    });
});
