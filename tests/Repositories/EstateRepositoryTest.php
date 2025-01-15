<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
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

    test('first', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(recordFactories: [
                EstateFactory::make()
                    ->id(1),
                EstateFactory::make()
                    ->id(2),
            ]),
        ]));

        $response = EstateRepository::query()->first();

        expect($response)->toBeArray()
            ->and($response['id'])->toBe(1);
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

    test('count', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                ReadEstateResponse::make(count: 1500),
            ]),
        ]);

        EstateRepository::record();

        $response = EstateRepository::query()->count();

        expect($response)->toBe(1500);

        EstateRepository::assertSentCount(1);
    });
});

describe('search', function () {
    it('should be able to build a search request', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                ReadEstateResponse::make(),
            ]),
        ]);

        EstateRepository::record();

        $builder = EstateRepository::query();
        $builder
            ->setInput('testInput')
            ->search();

        EstateRepository::assertSentCount(1);
        EstateRepository::assertSent(fn (OnOfficeRequest $request) => $request->resourceId === OnOfficeResourceId::Estate
            && $request->actionId === OnOfficeAction::Get
            && $request->resourceType === OnOfficeResourceType::Search
            && $request->parameters['input'] === 'testInput');
    });
});
