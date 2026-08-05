<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\LogRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\LogFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\ReadLogResponse;

describe('fake responses', function () {
    test('get', function () {
        LogRepository::fake(LogRepository::response([
            LogRepository::page(recordFactories: [
                LogFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = LogRepository::query()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        LogRepository::assertSentCount(1);
    });

    test('first', function () {
        LogRepository::fake(LogRepository::response([
            LogRepository::page(recordFactories: [
                LogFactory::make()->id(7),
                LogFactory::make()->id(8),
            ]),
        ]));

        $record = LogRepository::query()->first();

        expect($record['id'])->toBe(7);

        LogRepository::assertSentCount(1);
    });

    test('each', function () {
        LogRepository::fake(LogRepository::response([
            LogRepository::page(recordFactories: [
                LogFactory::make()->id(1),
                LogFactory::make()->id(2),
            ]),
        ]));

        $ids = [];
        LogRepository::query()->each(function (array $records) use (&$ids) {
            foreach ($records as $record) {
                $ids[] = $record['id'];
            }
        });

        expect($ids)->toBe([1, 2]);

        LogRepository::assertSentCount(1);
    });

    test('find', function () {
        LogRepository::fake(LogRepository::response([
            LogRepository::page(recordFactories: [
                LogFactory::make()->id(13),
            ]),
        ]));

        $record = LogRepository::query()->find(13);

        expect($record['id'])->toBe(13);

        LogRepository::assertSentCount(1);
    });

    test('read request carries module, action, user and filters', function () {
        LogRepository::fake(LogRepository::response([
            LogRepository::page(recordFactories: [
                LogFactory::make()->id(1),
            ]),
        ]));

        LogRepository::query()
            ->withModule('estate')
            ->withAction('edit')
            ->withUserId(42)
            ->where('actionId', 'create')
            ->get();

        LogRepository::assertSentCount(1);
        LogRepository::assertSent(fn (OnOfficeRequest $request): bool => $request->actionId === OnOfficeAction::Read
            && $request->resourceType === OnOfficeResourceType::Log
            && $request->parameters['module'] === 'estate'
            && $request->parameters['action'] === 'edit'
            && $request->parameters['user'] === 42
            && array_key_exists('actionId', $request->parameters['filter']));
    });

    test('read request omits user when no user id is set', function () {
        LogRepository::fake(LogRepository::response([
            LogRepository::page(recordFactories: [
                LogFactory::make()->id(1),
            ]),
        ]));

        LogRepository::query()->get();

        LogRepository::assertSent(fn (OnOfficeRequest $request): bool => ! array_key_exists('user', $request->parameters));
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                ReadLogResponse::make(),
            ]),
        ]);

        LogRepository::record();

        $response = LogRepository::query()->get();

        expect($response->count())->toBe(1);

        LogRepository::assertSentCount(1);
    });

    test('count', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                ReadLogResponse::make(count: 1500),
            ]),
        ]);

        LogRepository::record();

        $response = LogRepository::query()->count();

        expect($response)->toBe(1500);

        LogRepository::assertSentCount(1);
        LogRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Read
            && $request->resourceType === OnOfficeResourceType::Log
        );
    });
});
