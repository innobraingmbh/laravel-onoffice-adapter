<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\AddressRepository;
use Katalam\OnOfficeAdapter\Facades\EstateRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadAddressResponse;

describe('fake responses', function () {
    test('get', function () {
        AddressRepository::fake(AddressRepository::response([
            AddressRepository::page(recordFactories: [
                AddressFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = AddressRepository::query()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        AddressRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                ReadAddressResponse::make(count: 1500),
                ReadAddressResponse::make(count: 1500),
                ReadAddressResponse::make(count: 1500),
            ]),
        ]);

        AddressRepository::record();

        $response = AddressRepository::query()->get();

        expect($response->count())->toBe(3);

        AddressRepository::assertSentCount(3);
    });
});
