<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\TaskRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\TaskFactory;

describe('fake responses', function () {
    test('get', function () {
        TaskRepository::fake(TaskRepository::response([
            TaskRepository::page(recordFactories: [
                TaskFactory::make()->id(1),
                TaskFactory::make()->id(2),
            ]),
        ]));

        $response = TaskRepository::query()->get();

        expect($response->count())->toBe(2)
            ->and($response->first()['id'])->toBe(1);

        TaskRepository::assertSentCount(1);
    });

    test('first', function () {
        TaskRepository::fake(TaskRepository::response([
            TaskRepository::page(recordFactories: [
                TaskFactory::make()->id(1),
                TaskFactory::make()->id(2),
            ]),
        ]));

        $response = TaskRepository::query()->first();

        expect($response)->toBeArray()
            ->and($response['id'])->toBe(1);
    });

    test('find', function () {
        TaskRepository::fake(TaskRepository::response([
            TaskRepository::page(recordFactories: [
                TaskFactory::make()->id(42),
            ]),
        ]));

        $response = TaskRepository::query()->find(42);

        expect($response)->toBeArray()
            ->and($response['id'])->toBe(42);

        TaskRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Read
            && $request->resourceType === OnOfficeResourceType::Task
            && $request->resourceId === 42);
    });

    test('each', function () {
        TaskRepository::fake(TaskRepository::response([
            TaskRepository::page(recordFactories: [
                TaskFactory::make()->id(1),
                TaskFactory::make()->id(2),
            ]),
        ]));

        $seen = [];
        TaskRepository::query()->each(function (array $records) use (&$seen) {
            foreach ($records as $record) {
                $seen[] = $record['id'];
            }
        });

        expect($seen)->toBe([1, 2]);
    });

    test('create', function () {
        TaskRepository::fake(TaskRepository::response([
            TaskRepository::page(recordFactories: [
                TaskFactory::make()->id(7),
            ]),
        ]));

        $response = TaskRepository::query()->create([
            'Betreff' => 'Call client',
            'Status' => 1,
        ]);

        expect($response)->toBeArray()
            ->and($response['id'])->toBe(7);

        TaskRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Create
            && $request->resourceType === OnOfficeResourceType::Task
            && $request->parameters['data']['Betreff'] === 'Call client'
            && $request->parameters['data']['Status'] === 1);
    });

    test('modify', function () {
        TaskRepository::fake(TaskRepository::response([
            TaskRepository::page(recordFactories: [
                TaskFactory::make()->id(7),
            ]),
        ]));

        $result = TaskRepository::query()
            ->addModify('Status', 2)
            ->modify(7);

        expect($result)->toBeTrue();

        TaskRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Modify
            && $request->resourceType === OnOfficeResourceType::Task
            && $request->resourceId === 7
            && $request->parameters['data']['Status'] === 2);
    });

    test('modify forwards related setters to request parameters', function () {
        TaskRepository::fake(TaskRepository::response([
            TaskRepository::page(recordFactories: [
                TaskFactory::make()->id(7),
            ]),
        ]));

        TaskRepository::query()
            ->relatedEstateId(123)
            ->relatedAddressId(456)
            ->addModify('Status', 2)
            ->modify(7);

        TaskRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Modify
            && $request->resourceId === 7
            && $request->parameters['relatedEstateId'] === 123
            && $request->parameters['relatedAddressId'] === 456
            && $request->parameters['data']['Status'] === 2);
    });

    test('related setters are forwarded to request parameters', function () {
        TaskRepository::fake(TaskRepository::response([
            TaskRepository::page(recordFactories: [
                TaskFactory::make()->id(1),
            ]),
        ]));

        TaskRepository::query()
            ->relatedEstateId(123)
            ->relatedAddressId(456)
            ->relatedProjectIds([7, 8])
            ->get();

        TaskRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Read
            && $request->resourceType === OnOfficeResourceType::Task
            && $request->parameters['relatedEstateId'] === 123
            && $request->parameters['relatedAddressId'] === 456
            && $request->parameters['relatedProjectIds'] === [7, 8]);
    });

    test('relatedProjectIds passes through scalar and array values unchanged', function () {
        TaskRepository::fake([
            TaskRepository::response([
                TaskRepository::page(recordFactories: [TaskFactory::make()->id(1)]),
            ]),
            TaskRepository::response([
                TaskRepository::page(recordFactories: [TaskFactory::make()->id(2)]),
            ]),
        ]);

        TaskRepository::query()->relatedProjectIds(9)->get();
        TaskRepository::query()->relatedProjectIds([7, 8])->get();

        TaskRepository::assertSent(fn (OnOfficeRequest $request) => ($request->parameters['relatedProjectIds'] ?? null) === 9);
        TaskRepository::assertSent(fn (OnOfficeRequest $request) => ($request->parameters['relatedProjectIds'] ?? null) === [7, 8]);
    });
});

describe('real responses', function () {
    test('count', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => ['code' => 200],
                'response' => [
                    'results' => [
                        [
                            'data' => [
                                'meta' => ['cntabsolute' => 42],
                                'records' => [],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        TaskRepository::record();

        $response = TaskRepository::query()->count();

        expect($response)->toBe(42);

        TaskRepository::assertSentCount(1);
    });

    test('get builds the expected task read request', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => ['code' => 200],
                'response' => [
                    'results' => [
                        [
                            'data' => [
                                'meta' => ['cntabsolute' => 1],
                                'records' => [['id' => 1, 'type' => 'task']],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        TaskRepository::record();

        TaskRepository::query()
            ->select(['Betreff', 'Status'])
            ->relatedEstateId(123)
            ->get();

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Read->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::Task->value
                && data_get($body, 'request.actions.0.parameters.relatedEstateId') === 123
                && data_get($body, 'request.actions.0.parameters.data') === ['Betreff', 'Status'];
        });
    });
});
