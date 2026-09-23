<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\SearchCriteriaFactory;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Innobrain\OnOfficeAdapter\Tests\Stubs\CreateSearchCriteriaResponse;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetSearchCriteriaResponse;

describe('fake responses', function () {
    test('get', function () {
        SearchCriteriaRepository::fake(SearchCriteriaRepository::response([
            SearchCriteriaRepository::page(recordFactories: [
                SearchCriteriaFactory::make(),
            ]),
        ]));

        $response = SearchCriteriaRepository::query()->find(1);

        expect($response)->toBe([
            'id' => 0,
            'type' => '',
            'elements' => [],
        ]);

        SearchCriteriaRepository::assertSentCount(1);
    });

    test('get many reads every requested id in a single request', function () {
        SearchCriteriaRepository::fake(SearchCriteriaRepository::response([
            SearchCriteriaRepository::page(recordFactories: [
                SearchCriteriaFactory::make()->id(7),
                SearchCriteriaFactory::make()->id(8),
            ]),
        ]));

        $response = SearchCriteriaRepository::query()->recordIds([7, 8])->get();

        expect($response)->toHaveCount(2)
            ->and($response->pluck('id')->all())->toBe([7, 8]);

        SearchCriteriaRepository::assertSentCount(1);
        SearchCriteriaRepository::assertSent(fn (OnOfficeRequest $request): bool => $request->parameters[OnOfficeService::IDS] === [7, 8]
            && $request->parameters[OnOfficeService::MODE] === 'internal');
    });

    test('fields reads the search criteria field categories in one request', function () {
        SearchCriteriaRepository::fake(SearchCriteriaRepository::response([
            SearchCriteriaRepository::page(recordFactories: [
                SearchCriteriaFactory::make()->data([
                    'name' => 'Preise',
                    'fields' => [
                        ['id' => 'kaufpreis', 'name' => 'Purchase price', 'rangefield' => 'true'],
                    ],
                ]),
            ]),
        ]));

        $response = SearchCriteriaRepository::fields()
            ->parameter('language', 'ENG')
            ->get();

        expect($response)->toHaveCount(1)
            ->and($response->first()['elements']['fields'][0]['id'])->toBe('kaufpreis');

        SearchCriteriaRepository::assertSentCount(1);
        SearchCriteriaRepository::assertSent(fn (OnOfficeRequest $request): bool => $request->actionId === OnOfficeAction::Get
            && $request->resourceType === OnOfficeResourceType::SearchCriteriaFields
            && $request->parameters === ['language' => 'ENG']);
    });

    test('modify sends the changes as data of the search criteria', function () {
        SearchCriteriaRepository::fake(SearchCriteriaRepository::response([
            SearchCriteriaRepository::page(),
        ]));

        $result = SearchCriteriaRepository::query()
            ->addModify('kaufpreis__bis', '500000')
            ->addModify(['sys_ko' => ['kaufpreis']])
            ->modify(25);

        expect($result)->toBeTrue();

        SearchCriteriaRepository::assertSentCount(1);
        SearchCriteriaRepository::assertSent(fn (OnOfficeRequest $request): bool => $request->actionId === OnOfficeAction::Modify
            && $request->resourceType === OnOfficeResourceType::SearchCriteria
            && $request->resourceId === 25
            && $request->parameters === [
                OnOfficeService::DATA => ['kaufpreis__bis' => '500000', 'sys_ko' => ['kaufpreis']],
            ]);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                GetSearchCriteriaResponse::make(),
            ]),
        ]);

        SearchCriteriaRepository::record();

        $response = SearchCriteriaRepository::query()->find(1);

        expect($response)->toBe([]);

        SearchCriteriaRepository::assertSentCount(1);
    });

    test('create', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                CreateSearchCriteriaResponse::make(),
            ]),
        ]);

        SearchCriteriaRepository::record();

        $response = SearchCriteriaRepository::query()->find(1);

        expect($response)->toBe([
            'id' => 25,
            'type' => 'searchCriteria',
            'elements' => [],
        ]);

        SearchCriteriaRepository::assertSentCount(1);
    });
});
