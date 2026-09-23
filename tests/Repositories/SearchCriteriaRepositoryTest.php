<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeQueryException;
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

    test('filter mode sends the filter and sort order', function () {
        SearchCriteriaRepository::fake(SearchCriteriaRepository::response([
            SearchCriteriaRepository::page(recordFactories: [
                SearchCriteriaFactory::make()->id(47),
            ]),
        ]));

        $response = SearchCriteriaRepository::query()
            ->mode('filter')
            ->where('advisor', 12)
            ->whereBetween('creationdate', '2024-01-01', '2024-12-31')
            ->orderByDesc('creationdate')
            ->get();

        expect($response->pluck('id')->all())->toBe([47]);

        SearchCriteriaRepository::assertSentCount(1);
        SearchCriteriaRepository::assertSent(fn (OnOfficeRequest $request): bool => $request->actionId === OnOfficeAction::Get
            && $request->resourceType === OnOfficeResourceType::GetSearchCriteria
            && $request->parameters === [
                OnOfficeService::MODE => 'filter',
                OnOfficeService::FILTER => [
                    'advisor' => [['op' => '=', 'val' => 12]],
                    'creationdate' => [['op' => 'between', 'val' => ['2024-01-01', '2024-12-31']]],
                ],
                OnOfficeService::SORTBY => 'creationdate',
                OnOfficeService::SORTORDER => 'DESC',
                OnOfficeService::LISTLIMIT => 500,
                OnOfficeService::LISTOFFSET => 0,
            ]);
    });

    test('filter mode reads pages until one comes back short', function () {
        SearchCriteriaRepository::fake(SearchCriteriaRepository::response([
            SearchCriteriaRepository::page(recordFactories: [
                SearchCriteriaFactory::make()->id(1),
                SearchCriteriaFactory::make()->id(2),
            ], countAbsolute: 2),
            SearchCriteriaRepository::page(recordFactories: [
                SearchCriteriaFactory::make()->id(3),
            ], countAbsolute: 1),
        ]));

        $response = SearchCriteriaRepository::query()
            ->mode('filter')
            ->orderBy('internaladdressid')
            ->pageSize(2)
            ->get();

        expect($response->pluck('id')->all())->toBe([1, 2, 3]);

        SearchCriteriaRepository::assertSentCount(2);
    });

    test('filter mode stops reading at the limit', function () {
        SearchCriteriaRepository::fake(SearchCriteriaRepository::response([
            SearchCriteriaRepository::page(recordFactories: [
                SearchCriteriaFactory::make()->id(1),
                SearchCriteriaFactory::make()->id(2),
            ]),
        ]));

        $response = SearchCriteriaRepository::query()
            ->mode('filter')
            ->orderBy('internaladdressid')
            ->offset(10)
            ->limit(2)
            ->get();

        expect($response->pluck('id')->all())->toBe([1, 2]);

        SearchCriteriaRepository::assertSentCount(1);
        SearchCriteriaRepository::assertSent(fn (OnOfficeRequest $request): bool => $request->parameters[OnOfficeService::LISTLIMIT] === 2
            && $request->parameters[OnOfficeService::LISTOFFSET] === 10);
    });

    test('filter mode reads the first record', function () {
        SearchCriteriaRepository::fake(SearchCriteriaRepository::response([
            SearchCriteriaRepository::page(recordFactories: [
                SearchCriteriaFactory::make()->id(55),
            ]),
        ]));

        $record = SearchCriteriaRepository::query()
            ->mode('filter')
            ->orderByDesc('creationdate')
            ->first();

        expect($record['id'])->toBe(55);

        SearchCriteriaRepository::assertSent(fn (OnOfficeRequest $request): bool => $request->parameters[OnOfficeService::LISTLIMIT] === 1);
    });

    test('filter mode requires a sort order', function () {
        SearchCriteriaRepository::query()->mode('filter')->where('advisor', 12)->get();
    })->throws(OnOfficeQueryException::class);

    test('first requires filter mode', function () {
        SearchCriteriaRepository::query()->mode('searchcriteria')->first();
    })->throws(OnOfficeQueryException::class);

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

    test('matching searches the search criteria that fit the search data', function () {
        SearchCriteriaRepository::fake(SearchCriteriaRepository::response([
            SearchCriteriaRepository::page(recordFactories: [
                SearchCriteriaFactory::make()->id(18)->data(['Id' => '18', 'adresse' => '32']),
                SearchCriteriaFactory::make()->id(25)->data(['Id' => '25', 'adresse' => '8305']),
            ], countAbsolute: 2),
        ]));

        $response = SearchCriteriaRepository::matching(['vermarktungsart' => 'kauf'])
            ->searchData(['range_plz' => '52074'])
            ->select(['Id', 'adresse', 'kaufpreis__bis'])
            ->groupByAddress(false)
            ->orderByDesc('kaufpreis__bis')
            ->get();

        expect($response->pluck('elements.adresse')->all())->toBe(['32', '8305']);

        SearchCriteriaRepository::assertSentCount(1);
        SearchCriteriaRepository::assertSent(fn (OnOfficeRequest $request): bool => $request->actionId === OnOfficeAction::Get
            && $request->resourceType === OnOfficeResourceType::Search
            && $request->resourceId === OnOfficeResourceId::SearchCriteria
            && $request->parameters === [
                OnOfficeService::SEARCHDATA => ['vermarktungsart' => 'kauf', 'range_plz' => '52074'],
                OnOfficeService::OUTPUTALL => false,
                OnOfficeService::OUTPUTFIELDS => ['Id', 'adresse', 'kaufpreis__bis'],
                OnOfficeService::GROUPBYADDRESS => false,
                OnOfficeService::ORDER => ['kaufpreis__bis' => 'DESC'],
                OnOfficeService::LIMIT => 500,
                OnOfficeService::OFFSET => 0,
            ]);
    });

    test('matching pages with offset and limit instead of listoffset and listlimit', function () {
        SearchCriteriaRepository::fake(SearchCriteriaRepository::response([
            SearchCriteriaRepository::page(recordFactories: [
                SearchCriteriaFactory::make()->id(29),
            ], countAbsolute: 21),
        ]));

        $paginator = SearchCriteriaRepository::matching(['vermarktungsart' => 'kauf'])
            ->outputAll()
            ->paginate(perPage: 10, page: 3);

        expect($paginator->total())->toBe(21)
            ->and($paginator->items())->toHaveCount(1);

        SearchCriteriaRepository::assertSent(fn (OnOfficeRequest $request): bool => $request->parameters[OnOfficeService::OUTPUTALL] === true
            && $request->parameters[OnOfficeService::LIMIT] === 10
            && $request->parameters[OnOfficeService::OFFSET] === 20
            && ! array_key_exists(OnOfficeService::LISTLIMIT, $request->parameters)
            && ! array_key_exists(OnOfficeService::LISTOFFSET, $request->parameters));
    });

    test('matching counts with a limit of zero', function () {
        SearchCriteriaRepository::fake(SearchCriteriaRepository::response([
            SearchCriteriaRepository::page(countAbsolute: 18),
        ]));

        $count = SearchCriteriaRepository::matching(['vermarktungsart' => 'kauf'])->count();

        expect($count)->toBe(18);

        SearchCriteriaRepository::assertSent(fn (OnOfficeRequest $request): bool => $request->parameters[OnOfficeService::LIMIT] === 0);
    });

    test('matching cannot find a search criteria by id', function () {
        SearchCriteriaRepository::matching()->find(25);
    })->throws(OnOfficeQueryException::class);
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
