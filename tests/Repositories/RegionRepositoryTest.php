<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\RegionFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetRegionsResponse;

describe('fake responses', function () {
    test('get', function () {
        SettingRepository::fake(SettingRepository::response([
            SettingRepository::page(recordFactories: [
                RegionFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = SettingRepository::regions()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        SettingRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                GetRegionsResponse::make(),
            ]),
        ]);

        SettingRepository::record();

        $response = SettingRepository::regions()->get();

        expect($response->count())->toBe(1);

        SettingRepository::assertSentCount(1);
    });

    test('get does not paginate when the API reports a large absolute count', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                GetRegionsResponse::make(cntabsolute: 2000),
                GetRegionsResponse::make(cntabsolute: 2000),
                GetRegionsResponse::make(cntabsolute: 2000),
                GetRegionsResponse::make(cntabsolute: 2000),
            ]),
        ]);

        SettingRepository::record();

        $response = SettingRepository::regions()->get();

        expect($response->count())->toBe(1);

        SettingRepository::assertSentCount(1);
    });

    test('each does not paginate when the API reports a large absolute count', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                GetRegionsResponse::make(cntabsolute: 2000),
                GetRegionsResponse::make(cntabsolute: 2000),
                GetRegionsResponse::make(cntabsolute: 2000),
                GetRegionsResponse::make(cntabsolute: 2000),
            ]),
        ]);

        SettingRepository::record();

        $records = [];
        SettingRepository::regions()->each(function (array $page) use (&$records): void {
            $records = [...$records, ...$page];
        });

        expect($records)->toHaveCount(1);

        SettingRepository::assertSentCount(1);
    });
});
