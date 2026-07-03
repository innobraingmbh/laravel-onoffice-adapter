<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeRelationType;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\Query;
use Innobrain\OnOfficeAdapter\Facades\RelationRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\RelationFactory;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetEstateAgentsResponse;

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

describe('toRequest', function () {
    test('builds the ids-from-relation request without sending it', function () {
        $request = RelationRepository::query()
            ->relationType(OnOfficeRelationType::ContactPersonBroker)
            ->parentIds(5779)
            ->childIds([2169, 2205])
            ->toRequest();

        expect($request->actionId)->toBe(OnOfficeAction::Get)
            ->and($request->resourceType)->toBe(OnOfficeResourceType::IdsFromRelation)
            ->and($request->parameters)->toBe([
                OnOfficeService::RELATIONTYPE => OnOfficeRelationType::ContactPersonBroker,
                OnOfficeService::PARENTIDS => [5779],
                OnOfficeService::CHILDIDS => [2169, 2205],
            ]);
    });

    test('relation builders can be batched', function () {
        Query::fake(Query::response([
            Query::page(resourceType: OnOfficeResourceType::IdsFromRelation, recordFactories: [
                RelationFactory::make()->data([5779 => ['2169', '2205']]),
            ]),
            Query::page(resourceType: OnOfficeResourceType::IdsFromRelation, recordFactories: [
                RelationFactory::make()->data([608 => ['900']]),
            ]),
        ]));

        $results = Query::batch([
            RelationRepository::query()->relationType(OnOfficeRelationType::ContactPersonBroker)->parentIds(5779),
            RelationRepository::query()->relationType(OnOfficeRelationType::ContactPersonBroker)->childIds(608),
        ])->once();

        expect($results)->toHaveCount(2)
            ->and(data_get($results[0], 'data.records.0.elements'))->toBe([5779 => ['2169', '2205']])
            ->and(data_get($results[1], 'data.records.0.elements'))->toBe([608 => ['900']]);

        Query::assertSent(fn (OnOfficeRequest $request) => $request->resourceType === OnOfficeResourceType::IdsFromRelation
            && ($request->parameters[OnOfficeService::PARENTIDS] ?? null) === [5779]);
        Query::assertSent(fn (OnOfficeRequest $request) => ($request->parameters[OnOfficeService::CHILDIDS] ?? null) === [608]);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
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
