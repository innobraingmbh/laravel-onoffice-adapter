<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
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

    test('find is not supported', function () {
        SettingRepository::imprint()->find(1);
    })->throws(OnOfficeException::class, 'Not implemented');
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
