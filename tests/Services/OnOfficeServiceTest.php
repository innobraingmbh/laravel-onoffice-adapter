<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
use Katalam\OnOfficeAdapter\Tests\Stubs\InvalidHmacResponse;

it('can use the config token and secret', function () {
    $token = Str::random();
    $secret = Str::random();

    config([
        'onoffice.token' => $token,
        'onoffice.secret' => $secret,
    ]);

    $onOfficeService = app(OnOfficeService::class);

    expect($onOfficeService->getToken())->toBe($token)
        ->and($onOfficeService->getSecret())->toBe($secret);
});

describe('requestApi', function () {
    it('throws an exception on failed request', function (int $statusCode) {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'status' => [
                    'code' => $statusCode,
                ],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $onOfficeService->requestApi(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );
    })
        ->throws(OnOfficeException::class)
        ->with([300, 301, 400, 401, 500, 501]);

    it('throws an exception on failed request inside response', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => InvalidHmacResponse::make(),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $onOfficeService->requestApi(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );
    })
        ->throws(OnOfficeException::class);

});

describe('requestAll', function () {
    it('logs the request error', function (int $statusCode) {
        Log::shouldReceive('error')
            ->once()
            ->with('Error message');

        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'status' => [
                    'code' => $statusCode,
                    'message' => 'Error message',
                ],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $onOfficeService->requestAll(function () {
            app(OnOfficeService::class)->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::Estate,
            );
        });
    })->with([300, 301, 400, 401, 500, 501]);

    it('can handle null in result path', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'status' => [
                    'code' => 200,
                ],
                'response' => [
                    'results' => [
                        [
                            'data' => [
                                'meta' => [
                                    'cntabsolute' => 0,
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $response = $onOfficeService->requestAll(function () {
            return app(OnOfficeService::class)->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::Estate,
            );
        });

        expect($response)->toBeInstanceOf(Collection::class)
            ->toBeEmpty();
    });
});

describe('requestAllChunked', function () {
    it('logs the request error', function (int $statusCode) {
        Log::shouldReceive('error')
            ->once()
            ->with('Error message');

        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'status' => [
                    'code' => $statusCode,
                    'message' => 'Error message',
                ],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $onOfficeService->requestAllChunked(function () {
            app(OnOfficeService::class)->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::Estate,
            );
        }, function () {});
    })->with([300, 301, 400, 401, 500, 501]);

    it('will call the callback', function () {
        Http::fake([
            '*' => Http::response([
                'status' => [
                    'code' => 200,
                ],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $callback = Mockery::mock();
        $callback->shouldReceive('call')->once();

        $onOfficeService->requestAllChunked(function () {
            return app(OnOfficeService::class)->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::Estate,
            );
        }, function () use ($callback) {
            $callback->call();
        });
    });
});
