<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\SearchCriteriaRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\SearchCriteriaFactory;
use Katalam\OnOfficeAdapter\Tests\Stubs\GetSearchCriteriaResponse;

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
});
