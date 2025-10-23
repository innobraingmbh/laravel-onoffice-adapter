<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Facades\LastSeenRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\LastSeenFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\ReadLastSeenResponse;

describe('fake responses', function () {
    test('get', function () {
        LastSeenRepository::fake(LastSeenRepository::response([
            LastSeenRepository::page(recordFactories: [
                LastSeenFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = LastSeenRepository::query()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        LastSeenRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                ReadLastSeenResponse::make(),
            ]),
        ]);

        LastSeenRepository::record();

        $response = LastSeenRepository::query()->get();

        expect($response->count())->toBe(1);

        LastSeenRepository::assertSentCount(1);
    });
});
