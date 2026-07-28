<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Query;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateLanguageFactory;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetEstateLanguagesResponse;

describe('fake responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(
                actionId: OnOfficeAction::Get,
                resourceType: OnOfficeResourceType::EstateLanguage,
                recordFactories: [
                    EstateLanguageFactory::make()->id(31)->language('DEU')->isMain(true),
                    EstateLanguageFactory::make()->id(51)->language('ENG')->isMain(false),
                    EstateLanguageFactory::make()->id(53)->language('FRA')->isMain(false),
                ],
                countAbsolute: 3,
            ),
        ]));

        $response = EstateRepository::languages(31)->get();

        expect($response->count())->toBe(3)
            ->and($response->first()['id'])->toBe(31)
            ->and($response->first()['type'])->toBe('estateLanguage')
            ->and($response->pluck('elements.language')->all())->toBe(['DEU', 'ENG', 'FRA']);

        EstateRepository::assertSentCount(1);
    });

    test('first', function () {
        Http::preventStrayRequests();
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(
                actionId: OnOfficeAction::Get,
                resourceType: OnOfficeResourceType::EstateLanguage,
                recordFactories: [
                    EstateLanguageFactory::make()->id(31)->language('DEU')->isMain(true),
                    EstateLanguageFactory::make()->id(51)->language('ENG')->isMain(false),
                ],
                countAbsolute: 2,
            ),
        ]));

        $record = EstateRepository::languages(31)->first();

        expect($record)->toBeArray()
            ->and($record['id'])->toBe(31)
            ->and($record['elements']['language'])->toBe('DEU');

        EstateRepository::assertSentCount(1);
    });
});

describe('toRequest', function () {
    test('builds the estateLanguage request without sending it', function () {
        $request = EstateRepository::languages(31)->toRequest();

        expect($request->actionId)->toBe(OnOfficeAction::Get)
            ->and($request->resourceType)->toBe(OnOfficeResourceType::EstateLanguage)
            ->and($request->parameters)->toBe([OnOfficeService::ESTATEID => 31]);
    });

    test('estate language builders can be batched', function () {
        Query::fake(Query::response([
            Query::page(actionId: OnOfficeAction::Get, resourceType: OnOfficeResourceType::EstateLanguage, recordFactories: [
                EstateLanguageFactory::make()->id(31)->language('DEU'),
            ]),
            Query::page(actionId: OnOfficeAction::Get, resourceType: OnOfficeResourceType::EstateLanguage, recordFactories: [
                EstateLanguageFactory::make()->id(51)->language('ENG'),
            ]),
        ]));

        $results = Query::batch([
            EstateRepository::languages(31),
            EstateRepository::languages(51),
        ])->once();

        expect($results)->toHaveCount(2)
            ->and(data_get($results[0], 'data.records.0.elements.language'))->toBe('DEU')
            ->and(data_get($results[1], 'data.records.0.elements.language'))->toBe('ENG');

        Query::assertSent(fn (OnOfficeRequest $request) => $request->resourceType === OnOfficeResourceType::EstateLanguage
            && ($request->parameters[OnOfficeService::ESTATEID] ?? null) === 31);
        Query::assertSent(fn (OnOfficeRequest $request) => ($request->parameters[OnOfficeService::ESTATEID] ?? null) === 51);
    });
});

describe('real responses', function () {
    test('get returns language records from the response', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => GetEstateLanguagesResponse::make(),
        ]);

        EstateRepository::record();

        $records = EstateRepository::languages(31)->get();

        expect($records)->toHaveCount(3)
            ->and($records->pluck('elements.language')->all())->toBe(['DEU', 'ENG', 'FRA']);

        EstateRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Get
            && $request->resourceType === OnOfficeResourceType::EstateLanguage
            && ($request->parameters[OnOfficeService::ESTATEID] ?? null) === 31);
        EstateRepository::assertSentCount(1);
    });

    test('get does not paginate when the API reports a large absolute count', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                GetEstateLanguagesResponse::make(cntabsolute: 2000),
                GetEstateLanguagesResponse::make(cntabsolute: 2000),
                GetEstateLanguagesResponse::make(cntabsolute: 2000),
                GetEstateLanguagesResponse::make(cntabsolute: 2000),
            ]),
        ]);

        EstateRepository::record();

        $records = EstateRepository::languages(31)->get();

        expect($records)->toHaveCount(3);

        EstateRepository::assertSentCount(1);
    });

    test('first returns the first language record from the response', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => GetEstateLanguagesResponse::make(),
        ]);

        EstateRepository::record();

        $record = EstateRepository::languages(31)->first();

        expect($record)->toBeArray()
            ->and($record['elements']['language'])->toBe('DEU');

        EstateRepository::assertSentCount(1);
    });
});
