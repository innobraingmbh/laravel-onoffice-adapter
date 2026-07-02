<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\ImprintFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetImprintResponse;

describe('fake responses', function () {
    test('get', function () {
        SettingRepository::fake(SettingRepository::response([
            SettingRepository::page(recordFactories: [
                ImprintFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = SettingRepository::imprint()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        SettingRepository::assertSentCount(1);
    });

    test('first', function () {
        SettingRepository::fake(SettingRepository::response([
            SettingRepository::page(recordFactories: [
                ImprintFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = SettingRepository::imprint()->first();

        expect($response['id'])->toBe(1);
    });

    test('find', function () {
        SettingRepository::fake(SettingRepository::response([
            SettingRepository::page(recordFactories: [
                ImprintFactory::make()
                    ->id(5),
            ]),
        ]));

        $response = SettingRepository::imprint()->find(5);

        expect($response['id'])->toBe(5);

        SettingRepository::assertSent(fn (OnOfficeRequest $request) => $request->resourceId === 5);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                GetImprintResponse::make(),
            ]),
        ]);

        SettingRepository::record();

        $response = SettingRepository::imprint()->get();

        expect($response->count())->toBe(1);

        SettingRepository::assertSentCount(1);
    });
});
