<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstatePictureFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetEstateFilesResponse;

describe('fake responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(recordFactories: [
                EstatePictureFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = EstateRepository::pictures(1)->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1)
            ->and($response->first()['type'])->toBe('estatepictures')
            ->and($response->first()['elements']['url'])->toBe('https://via.placeholder.com/150')
            ->and($response->first()['elements']['estateId'])->toBe(1);

        EstateRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                GetEstateFilesResponse::make(count: 1500),
                GetEstateFilesResponse::make(count: 1500),
                GetEstateFilesResponse::make(count: 1500),
            ]),
        ]);

        EstateRepository::record();

        $response = EstateRepository::files(1)->get();

        expect($response->count())->toBe(6);

        EstateRepository::assertSentCount(3);
    });
});