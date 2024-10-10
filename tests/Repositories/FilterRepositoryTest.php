<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeQueryException;
use Innobrain\OnOfficeAdapter\Facades\FilterRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\FilterFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetFiltersResponse;

describe('fake responses', function () {
    test('get', function () {
        FilterRepository::fake(FilterRepository::response([
            FilterRepository::page(recordFactories: [
                FilterFactory::make()
                    ->id(1)
                    ->data([
                        'scope' => 'office',
                        'name' => 'Büroadressen',
                        'userId' => null,
                        'groupId' => 195,
                    ]),
            ]),
        ]));

        $response = FilterRepository::query()->address()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        FilterRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                GetFiltersResponse::make(count: 1500),
                GetFiltersResponse::make(count: 1500),
                GetFiltersResponse::make(count: 1500),
            ]),
        ]);

        FilterRepository::record();

        $response = FilterRepository::query()->estate()->get();

        expect($response->count())->toBe(4500);

        FilterRepository::assertSentCount(3);
    });
});

describe('query exception', function () {
    it('will throw a query exception when module is missing', function (string $method) {
        Http::preventStrayRequests();

        FilterRepository::query()->{$method}(fn () => null);
    })
        ->with([
            'get',
            'first',
            'each',
        ])
        ->throws(OnOfficeQueryException::class, 'Filter Builder module is not set');
});
