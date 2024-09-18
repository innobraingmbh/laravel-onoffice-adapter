<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\FieldRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\FieldFactory;
use Katalam\OnOfficeAdapter\Tests\Stubs\GetFieldsResponse;

describe('fake responses', function () {
    test('get', function () {
        FieldRepository::fake(FieldRepository::response([
            FieldRepository::page(recordFactories: [
                FieldFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = FieldRepository::query()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        FieldRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                GetFieldsResponse::make(),
            ]),
        ]);

        FieldRepository::record();

        $response = FieldRepository::query()->get();

        expect($response->count())->toBe(13);

        FieldRepository::assertSentCount(1);
    });
});
