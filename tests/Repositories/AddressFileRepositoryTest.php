<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\FileFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetAddressFilesResponse;

describe('fake responses', function () {
    test('get', function () {
        AddressRepository::fake(AddressRepository::response([
            AddressRepository::page(recordFactories: [
                FileFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = AddressRepository::files(1)->get();

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
                GetAddressFilesResponse::make(count: 1500),
                GetAddressFilesResponse::make(count: 1500),
                GetAddressFilesResponse::make(count: 1500),
            ]),
        ]);

        AddressRepository::record();

        $response = AddressRepository::files(1)->get();

        expect($response->count())->toBe(6);

        AddressRepository::assertSentCount(3);
    });
});
