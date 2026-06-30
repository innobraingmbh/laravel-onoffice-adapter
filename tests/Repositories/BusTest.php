<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Exceptions\StrayRequestException;
use Innobrain\OnOfficeAdapter\Facades\Bus;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

describe('fake responses', function () {
    test('once returns one result per action', function () {
        Bus::fake(Bus::response([
            Bus::page(recordFactories: [
                EstateFactory::make()->id(1),
            ]),
            Bus::page(resourceType: OnOfficeResourceType::Address, recordFactories: [
                AddressFactory::make()->id(2),
            ]),
        ]));

        $results = Bus::batch([
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate),
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Address),
        ])->once();

        expect($results)->toHaveCount(2)
            ->and(data_get($results[0], 'resourcetype'))->toBe('estate')
            ->and(data_get($results[0], 'data.records.0.id'))->toBe(1)
            ->and(data_get($results[1], 'resourcetype'))->toBe('address')
            ->and(data_get($results[1], 'data.records.0.id'))->toBe(2);
    });

    test('each action is recorded with its own result', function () {
        Bus::fake(Bus::response([
            Bus::page(recordFactories: [
                EstateFactory::make()->id(1),
            ]),
            Bus::page(resourceType: OnOfficeResourceType::Address, recordFactories: [
                AddressFactory::make()->id(2),
            ]),
        ]));

        Bus::batch([
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate),
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Address),
        ])->once();

        Bus::assertSentCount(2);
        Bus::assertSent(fn (OnOfficeRequest $request) => $request->resourceType === OnOfficeResourceType::Address);

        expect(data_get(Bus::lastRecordedResponse(), 'response.results.0.data.records.0.id'))->toBe(2);
    });

    test('builders can be added directly', function () {
        Bus::fake(Bus::response([
            Bus::page(recordFactories: [
                EstateFactory::make()->id(1),
            ]),
        ]));

        $results = Bus::batch([
            EstateRepository::query()->select('kaufpreis')->limit(5),
        ])->once();

        expect($results)->toHaveCount(1);

        Bus::assertSent(function (OnOfficeRequest $request) {
            return $request->actionId === OnOfficeAction::Read
                && $request->resourceType === OnOfficeResourceType::Estate
                && data_get($request->parameters, 'data') === ['kaufpreis']
                && data_get($request->parameters, 'listlimit') === 5;
        });
    });

    test('sending an empty batch throws', function () {
        Bus::fake(null);

        Bus::batch()->once();
    })->throws(OnOfficeException::class, 'Cannot send an empty batch');

    test('stray requests are prevented', function () {
        Bus::preventStrayRequests();

        Bus::batch([
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate),
        ])->once();
    })->throws(StrayRequestException::class);

    test('a failed action throws', function () {
        Bus::fake(Bus::response([
            Bus::page(recordFactories: [
                EstateFactory::make()->id(1),
            ]),
            Bus::page(resourceType: OnOfficeResourceType::Address, errorCodeResult: 137, messageResult: 'Error'),
        ]));

        Bus::batch([
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate),
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Address),
        ])->once();
    })->throws(OnOfficeException::class, 'Error');
});

describe('real responses', function () {
    test('once posts all actions in one request', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => ['code' => 200, 'errorcode' => 0, 'message' => 'OK'],
                'response' => [
                    'results' => [
                        [
                            'actionid' => OnOfficeAction::Read->value,
                            'resourcetype' => 'estate',
                            'identifier' => 'estates',
                            'data' => ['meta' => ['cntabsolute' => 1], 'records' => [['id' => 1, 'type' => 'estate', 'elements' => []]]],
                            'status' => ['errorcode' => 0, 'message' => 'OK'],
                        ],
                        [
                            'actionid' => OnOfficeAction::Read->value,
                            'resourcetype' => 'address',
                            'identifier' => 'addresses',
                            'data' => ['meta' => ['cntabsolute' => 1], 'records' => [['id' => 2, 'type' => 'address', 'elements' => []]]],
                            'status' => ['errorcode' => 0, 'message' => 'OK'],
                        ],
                    ],
                ],
            ]),
        ]);

        $results = Bus::batch([
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate, identifier: 'estates'),
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Address, identifier: 'addresses'),
        ])->once();

        expect($results)->toHaveCount(2)
            ->and(data_get($results->firstWhere('identifier', 'addresses'), 'data.records.0.id'))->toBe(2);

        Http::assertSent(function (Request $request) {
            $actions = data_get($request->data(), 'request.actions');

            return count($actions) === 2
                && data_get($actions, '0.resourcetype') === 'estate'
                && data_get($actions, '0.identifier') === 'estates'
                && data_get($actions, '0.hmac') !== ''
                && data_get($actions, '1.resourcetype') === 'address';
        });

        Http::assertSentCount(1);
    });

    test('the signature is generated on once(), not when the batch is built', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => ['code' => 200, 'errorcode' => 0, 'message' => 'OK'],
                'response' => [
                    'results' => [
                        [
                            'actionid' => OnOfficeAction::Read->value,
                            'resourcetype' => 'estate',
                            'data' => ['meta' => ['cntabsolute' => 0], 'records' => []],
                            'status' => ['errorcode' => 0, 'message' => 'OK'],
                        ],
                    ],
                ],
            ]),
        ]);

        Carbon::setTestNow('2026-01-01 12:00:00');

        $batch = Bus::batch([
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate),
        ]);

        Carbon::setTestNow('2026-01-01 12:30:00');

        $batch->once();

        $dispatchedAt = Carbon::parse('2026-01-01 12:30:00')->timestamp;

        Http::assertSent(fn (Request $request): bool => data_get($request->data(), 'request.actions.0.timestamp') === $dispatchedAt);

        Carbon::setTestNow();
    });

    test('a failed action throws', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => ['code' => 200, 'errorcode' => 0, 'message' => 'OK'],
                'response' => [
                    'results' => [
                        [
                            'actionid' => OnOfficeAction::Read->value,
                            'resourcetype' => 'estate',
                            'data' => ['meta' => ['cntabsolute' => 1], 'records' => []],
                            'status' => ['errorcode' => 0, 'message' => 'OK'],
                        ],
                        [
                            'actionid' => OnOfficeAction::Read->value,
                            'resourcetype' => 'address',
                            'data' => ['meta' => ['cntabsolute' => 0], 'records' => []],
                            'status' => ['errorcode' => 137, 'message' => 'Some error'],
                        ],
                    ],
                ],
            ]),
        ]);

        Bus::batch([
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate),
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Address),
        ])->once();
    })->throws(OnOfficeException::class, 'Some error');
});
