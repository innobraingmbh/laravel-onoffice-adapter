<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\ReadAddressResponse;

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
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
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

    test('count', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                ReadAddressResponse::make(count: 1500),
            ]),
        ]);

        AddressRepository::record();

        $response = AddressRepository::query()->count();

        expect($response)->toBe(1500);

        AddressRepository::assertSentCount(1);
    });
});

describe('search', function () {
    it('should be able to build a search request', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                ReadAddressResponse::make(),
            ]),
        ]);

        AddressRepository::record();

        $builder = AddressRepository::query();
        $builder
            ->setInput('testInput')
            ->search();

        AddressRepository::assertSentCount(1);
        AddressRepository::assertSent(fn (OnOfficeRequest $request) => $request->resourceId === OnOfficeResourceId::Address
            && $request->actionId === OnOfficeAction::Get
            && $request->resourceType === OnOfficeResourceType::Search
            && $request->parameters['input'] === 'testInput');
    });
});
