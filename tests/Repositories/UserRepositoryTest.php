<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\UserFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\ReadUserResponse;

describe('fake responses', function () {
    test('get', function () {
        SettingRepository::fake(SettingRepository::response([
            SettingRepository::page(recordFactories: [
                UserFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = SettingRepository::users()->get();

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
                ReadUserResponse::make(),
            ]),
        ]);

        SettingRepository::record();

        $response = SettingRepository::users()->get();

        expect($response->count())->toBe(1);

        SettingRepository::assertSentCount(1);
    });

    test('count', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                ReadUserResponse::make(count: 1500),
            ]),
        ]);

        SettingRepository::record();

        $response = SettingRepository::users()->count();

        expect($response)->toBe(1500);

        SettingRepository::assertSentCount(1);
    });
});
