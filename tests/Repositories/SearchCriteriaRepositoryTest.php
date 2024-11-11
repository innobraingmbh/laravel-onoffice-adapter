<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\SearchCriteriaFactory;
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
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
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
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
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
