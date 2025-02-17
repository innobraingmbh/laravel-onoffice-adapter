<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Facades\LogRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\LogFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\ReadLogResponse;

describe('fake responses', function () {
    test('get', function () {
        LogRepository::fake(LogRepository::response([
            LogRepository::page(recordFactories: [
                LogFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = LogRepository::query()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        LogRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                ReadLogResponse::make(),
            ]),
        ]);

        LogRepository::record();

        $response = LogRepository::query()->get();

        expect($response->count())->toBe(1);

        LogRepository::assertSentCount(1);
    });

    test('count', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                ReadLogResponse::make(count: 1500),
            ]),
        ]);

        LogRepository::record();

        $response = LogRepository::query()->count();

        expect($response)->toBe(1500);

        LogRepository::assertSentCount(1);
    });
});
