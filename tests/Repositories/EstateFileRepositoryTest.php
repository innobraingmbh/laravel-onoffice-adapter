<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\ActivityRepository;
use Katalam\OnOfficeAdapter\Facades\EstateRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\FileFactory;
use Katalam\OnOfficeAdapter\Tests\Stubs\GetEstatePicturesResponse;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadActivityResponse;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadAddressResponse;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadEstateResponse;

describe('fake responses', function () {
    test('get', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(recordFactories: [
                FileFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = EstateRepository::files(1)->get();

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
                GetEstatePicturesResponse::make(count: 1500),
                GetEstatePicturesResponse::make(count: 1500),
                GetEstatePicturesResponse::make(count: 1500),
            ]),
        ]);

        EstateRepository::record();

        $response = EstateRepository::files(1)->get();

        expect($response->count())->toBe(6);

        EstateRepository::assertSentCount(3);
    });
});
