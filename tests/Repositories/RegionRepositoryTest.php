<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\SettingRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\RegionFactory;
use Katalam\OnOfficeAdapter\Tests\Stubs\GetRegionsResponse;

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
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                GetRegionsResponse::make(),
            ]),
        ]);

        SettingRepository::record();

        $response = SettingRepository::regions()->get();

        expect($response->count())->toBe(1);

        SettingRepository::assertSentCount(1);
    });
});
