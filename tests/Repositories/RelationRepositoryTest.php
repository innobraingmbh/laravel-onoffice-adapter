<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Enums\OnOfficeRelationType;
use Katalam\OnOfficeAdapter\Facades\RelationRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\RelationFactory;
use Katalam\OnOfficeAdapter\Tests\Stubs\GetEstateAgentsResponse;

describe('fake responses', function () {
    test('get', function () {
        RelationRepository::fake(RelationRepository::response([
            RelationRepository::page(recordFactories: [
                RelationFactory::make()
                    ->data([
                        5779 => [
                            '2169',
                            '2205',
                        ],
                    ]),
            ]),
        ]));

        $response = RelationRepository::query()
            ->relationType(OnOfficeRelationType::ContactPersonBroker)
            ->get();

        expect($response->count())->toBe(1);

        RelationRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                GetEstateAgentsResponse::make(),
            ]),
        ]);

        RelationRepository::record();

        $response = RelationRepository::query()
            ->relationType(OnOfficeRelationType::ContactPersonBroker)
            ->get();

        expect($response->count())->toBe(6);

        RelationRepository::assertSentCount(1);
    });
});
