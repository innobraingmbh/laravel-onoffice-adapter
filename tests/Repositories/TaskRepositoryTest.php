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
                TaskFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = TaskRepository::query()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        TaskRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                ReadTaskResponse::make(count: 1500),
                ReadTaskResponse::make(count: 1500),
                ReadTaskResponse::make(count: 1500),
            ]),
        ]);

        TaskRepository::record();

        $response = TaskRepository::query()->get();

        expect($response->count())->toBe(3);

        TaskRepository::assertSentCount(3);
    });

    test('count', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                ReadTaskResponse::make(count: 1500),
            ]),
        ]);

        TaskRepository::record();

        $response = TaskRepository::query()->count();

        expect($response)->toBe(1500);

        TaskRepository::assertSentCount(1);
    });
});
