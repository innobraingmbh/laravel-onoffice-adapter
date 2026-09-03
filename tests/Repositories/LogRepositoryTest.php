<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\LogRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\LogFactory;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
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

describe('read request shape', function () {
    test('get, first, each and count all build the same Read request', function () {
        LogRepository::fake([
            LogRepository::response(),
            LogRepository::response(),
            LogRepository::response(),
            LogRepository::response(),
        ]);

        LogRepository::query()->withModule('estate')->withAction('edit')->get();
        LogRepository::query()->withModule('estate')->withAction('edit')->first();
        LogRepository::query()->withModule('estate')->withAction('edit')->each(fn () => null);
        LogRepository::query()->withModule('estate')->withAction('edit')->count();

        LogRepository::assertSentCount(4);
        LogRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Read
            && $request->resourceType === OnOfficeResourceType::Log
            && $request->parameters[OnOfficeService::MODULE] === 'estate'
            && $request->parameters[OnOfficeService::ACTION] === 'edit'
            && ! array_key_exists(OnOfficeService::USER, $request->parameters)
        );
    });

    test('withUserId adds the user parameter, and its default omits it', function () {
        LogRepository::fake([
            LogRepository::response(),
            LogRepository::response(),
        ]);

        LogRepository::query()->withUserId(42)->get();
        LogRepository::query()->get();

        LogRepository::assertSent(fn (OnOfficeRequest $request) => ($request->parameters[OnOfficeService::USER] ?? null) === 42);
        LogRepository::assertSent(fn (OnOfficeRequest $request) => ! array_key_exists(OnOfficeService::USER, $request->parameters));
    });
});
