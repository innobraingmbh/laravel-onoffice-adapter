<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeQueryException;
use Innobrain\OnOfficeAdapter\Query\ActivityBuilder;
use Innobrain\OnOfficeAdapter\Repositories\ActivityRepository;

describe('new estate/address methods', function () {
    it('sets estateId parameter correctly', function () {
        $builder = new ActivityBuilder;

        $builder->estateId(123);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe(['estateid' => 123]);
    });

    it('sets addressIds parameter correctly with single ID', function () {
        $builder = new ActivityBuilder;

        $builder->addressIds(123);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe(['addressid' => [123]]);
    });

    it('sets addressIds parameter correctly with array of IDs', function () {
        $builder = new ActivityBuilder;

        $builder->addressIds([1, 2, 3]);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe(['addressid' => [1, 2, 3]]);
    });

    it('sets addressIds parameter correctly with array of IDs when calling create', function () {
        $builder = new ActivityBuilder;

        $builder->addressIds([1, 2, 3]);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $parameters = $m->invoke($builder, true);

        expect($parameters)->toBe(['addressids' => [1, 2, 3]]);
    });

    it('combines estateId and addressIds parameters correctly', function () {
        $builder = new ActivityBuilder;

        $builder
            ->estateId(123)
            ->addressIds([1, 2, 3]);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe([
            'estateid' => 123,
            'addressid' => [1, 2, 3],
        ]);
    });

    it('sets estateIds parameter correctly with array of IDs', function () {
        $builder = new ActivityBuilder;

        $builder->estateIds([1, 2, 3]);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe(['estateid' => [1, 2, 3]]);
    });

    it('sets estateIds parameter correctly with single ID', function () {
        $builder = new ActivityBuilder;

        $builder->estateIds(123);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe(['estateid' => [123]]);
    });

    it('uses whichever of estateId and estateIds was called last', function () {
        $m = new ReflectionMethod(ActivityBuilder::class, 'prepareEstateOrAddressParameters');

        $estateIdsLast = (new ActivityBuilder)->estateId(123)->estateIds([1, 2]);
        $estateIdLast = (new ActivityBuilder)->estateIds([1, 2])->estateId(123);

        expect($m->invoke($estateIdsLast))->toBe(['estateid' => [1, 2]])
            ->and($m->invoke($estateIdLast))->toBe(['estateid' => 123]);
    });
});

describe('CRUD operations', function () {
    beforeEach(function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => [
                    'code' => 200,
                ],
                'response' => [
                    'results' => [
                        [
                            'data' => [
                                'records' => [
                                    ['id' => 1, 'type' => 'activity'],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    });

    it('creates activity with estate parameters', function () {
        $builder = new ActivityBuilder;

        $builder
            ->setRepository(new ActivityRepository)
            ->estateId(123)
            ->create(['note' => 'Test activity']);

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.parameters.estateid') === 123
                && data_get($body, 'request.actions.0.parameters.note') === 'Test activity';
        });
    });

    it('gets activities with combined parameters', function () {
        $builder = new ActivityBuilder;

        $builder
            ->setRepository(new ActivityRepository)
            ->estateId(123)
            ->addressIds([1, 2])
            ->get();

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.parameters.estateid') === 123
                && data_get($body, 'request.actions.0.parameters.addressid') === [1, 2]
                && data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Read->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::Activity->value;
        });
    });

    it('gets activities of several estates', function () {
        $builder = new ActivityBuilder;

        $builder
            ->setRepository(new ActivityRepository)
            ->estateIds([123, 124])
            ->get();

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.parameters.estateid') === [123, 124]
                && data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Read->value;
        });
    });

    it('refuses to create an activity linked to several estates', function () {
        $builder = new ActivityBuilder;

        expect(fn () => $builder
            ->setRepository(new ActivityRepository)
            ->estateIds([123, 124])
            ->create(['note' => 'Test activity']))
            ->toThrow(OnOfficeQueryException::class, 'Use estateId() to create one.');

        Http::assertNothingSent();
    });

    it('creates activities with combined parameters', function () {
        $builder = new ActivityBuilder;

        $builder
            ->setRepository(new ActivityRepository)
            ->estateId(123)
            ->addressIds([1, 2])
            ->create(['note' => 'Test activity']);

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.parameters.estateid') === 123
                && data_get($body, 'request.actions.0.parameters.addressids') === [1, 2]
                && data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Create->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::Activity->value;
        });
    });
});
