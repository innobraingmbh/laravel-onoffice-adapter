<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Query\ActivityBuilder;
use Innobrain\OnOfficeAdapter\Repositories\ActivityRepository;

describe('deprecated estate/address methods', function () {
    it('sets estate parameter correctly', function () {
        $builder = new ActivityBuilder;

        $builder->estate();
        $builder->recordIds([1, 2, 3]);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $m->setAccessible(true);
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe(['estateid' => [1, 2, 3]]);
    });

    it('sets address parameter correctly', function () {
        $builder = new ActivityBuilder;

        $builder->address();
        $builder->recordIds([1, 2, 3]);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $m->setAccessible(true);
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe(['addressids' => [1, 2, 3]]);
    });

    it('sets estate parameter via recordIdsAsEstate', function () {
        $builder = new ActivityBuilder;

        $builder->recordIdsAsEstate();
        $builder->recordIds([1, 2, 3]);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $m->setAccessible(true);
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe(['estateid' => [1, 2, 3]]);
    });

    it('sets address parameter via recordIdsAsAddress', function () {
        $builder = new ActivityBuilder;

        $builder->recordIdsAsAddress();
        $builder->recordIds([1, 2, 3]);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $m->setAccessible(true);
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe(['addressids' => [1, 2, 3]]);
    });
});

describe('new estate/address methods', function () {
    it('sets estateId parameter correctly', function () {
        $builder = new ActivityBuilder;

        $builder->estateId(123);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $m->setAccessible(true);
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe(['estateid' => 123]);
    });

    it('sets addressIds parameter correctly with single ID', function () {
        $builder = new ActivityBuilder;

        $builder->addressIds(123);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $m->setAccessible(true);
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe(['addressids' => [123]]);
    });

    it('sets addressIds parameter correctly with array of IDs', function () {
        $builder = new ActivityBuilder;

        $builder->addressIds([1, 2, 3]);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $m->setAccessible(true);
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe(['addressids' => [1, 2, 3]]);
    });

    it('combines estateId and addressIds parameters correctly', function () {
        $builder = new ActivityBuilder;

        $builder
            ->estateId(123)
            ->addressIds([1, 2, 3]);

        $m = new ReflectionMethod($builder, 'prepareEstateOrAddressParameters');
        $m->setAccessible(true);
        $parameters = $m->invoke($builder);

        expect($parameters)->toBe([
            'estateid' => 123,
            'addressids' => [1, 2, 3],
        ]);
    });
});

describe('CRUD operations', function () {
    beforeEach(function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::response([
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
                && data_get($body, 'request.actions.0.parameters.addressids') === [1, 2]
                && data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Read->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::Activity->value;
        });
    });
});
