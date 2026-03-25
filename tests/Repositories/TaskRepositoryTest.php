<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Facades\TaskRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\TaskFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\ReadTaskResponse;

describe('fake responses', function () {
    test('get', function () {
        TaskRepository::fake(TaskRepository::response([
            TaskRepository::page(recordFactories: [
                TaskFactory::make()->id(1),
            ]),
        ]));

        $response = TaskRepository::query()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        TaskRepository::assertSentCount(1);
    });

    test('count', function () {
        TaskRepository::fake(TaskRepository::response([
            TaskRepository::page(countAbsolute: 42),
        ]));

        $count = TaskRepository::query()->count();

        expect($count)->toBe(42);

        TaskRepository::assertSentCount(1);
    });

    test('first', function () {
        TaskRepository::fake(TaskRepository::response([
            TaskRepository::page(recordFactories: [
                TaskFactory::make()->id(7),
            ]),
        ]));

        $record = TaskRepository::query()->first();

        expect($record['id'])->toBe(7);

        TaskRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                ReadTaskResponse::make(count: 1),
            ]),
        ]);

        TaskRepository::record();

        $response = TaskRepository::query()->get();

        expect($response->count())->toBe(1);

        TaskRepository::assertSentCount(1);
    });

    test('count', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                ReadTaskResponse::make(count: 99),
            ]),
        ]);

        TaskRepository::record();

        $count = TaskRepository::query()->count();

        expect($count)->toBe(99);

        TaskRepository::assertSentCount(1);
    });
});
