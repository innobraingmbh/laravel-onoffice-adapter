<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeError;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
use Katalam\OnOfficeAdapter\Tests\Stubs\InvalidHmacResponse;

describe('credentials', function () {
    it('can use the config once', function () {
        $token = Str::random();
        $secret = Str::random();
        $apiClaim = Str::random();

        config([
            'onoffice.token' => $token,
            'onoffice.secret' => $secret,
            'onoffice.api_claim' => $apiClaim,
        ]);

        $onOfficeService = app(OnOfficeService::class);

        expect($onOfficeService->getToken())->toBe($token)
            ->and($onOfficeService->getSecret())->toBe($secret)
            ->and($onOfficeService->getApiClaim())->toBe($apiClaim);
    });

    it('can use the config twice', function () {
        $token = Str::random();
        $secret = Str::random();
        $apiClaim = Str::random();

        config([
            'onoffice.token' => 'old-token',
            'onoffice.secret' => 'old-secret',
            'onoffice.api_claim' => 'old-claim',
        ]);

        $onOfficeService = app(OnOfficeService::class);

        config([
            'onoffice.token' => $token,
            'onoffice.secret' => $secret,
            'onoffice.api_claim' => $apiClaim,
        ]);

        expect($onOfficeService->getToken())->toBe($token)
            ->and($onOfficeService->getSecret())->toBe($secret)
            ->and($onOfficeService->getApiClaim())->toBe($apiClaim);
    });
});

it('signs the exact timestamp included in the request', function () {
    $token = str_repeat('t', 32);
    $secret = str_repeat('s', 64);
    $timestamp = Carbon::create(2026, 7, 2, 23, 59, 59)->timestamp;

    config([
        'onoffice.token' => $token,
        'onoffice.secret' => $secret,
    ]);

    Carbon::setTestNow(Carbon::createFromTimestampUTC($timestamp + 1));

    $getHmac = new ReflectionMethod(OnOfficeService::class, 'getHmac');
    $hmac = $getHmac->invoke(
        app(OnOfficeService::class),
        OnOfficeAction::Read,
        OnOfficeResourceType::Address,
        $timestamp,
    );

    Carbon::setTestNow();

    expect($hmac)->toBe(base64_encode(hash_hmac(
        'sha256',
        $timestamp.$token.OnOfficeResourceType::Address->value.OnOfficeAction::Read->value,
        $secret,
        true,
    )));
});

describe('exceptions', function () {
    it('throws an exception on status code', function (int $statusCode) {
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

    it('throws an exception on status error code', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'status' => [
                    'code' => 500,
                    'errorcode' => 41,
                    'message' => 'Customer unknown!',
                ],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        expect(
            fn () => $onOfficeService->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::Estate,
            )
        )->toThrow(OnOfficeException::class, 'Customer unknown!');
    });

    it('throws an exception on failed request inside response', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => InvalidHmacResponse::make(),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        expect(
            fn () => $onOfficeService->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::Estate,
            )
        )->toThrow(OnOfficeException::class, 'The HMAC is invalid');
    });

    it('can return an error', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => InvalidHmacResponse::make(),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        try {
            $onOfficeService->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::Estate,
            );
        } catch (OnOfficeException $exception) {
            expect($exception->getError())->toBe(OnOfficeError::The_HMAC_Is_Invalid);
        }
    });
});

describe('requestAll', function () {
    it('throws the request error', function (int $statusCode) {
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
    })->with([300, 301, 400, 401, 500, 501])->throws(OnOfficeException::class);

    it('throws when a later page fails instead of returning partial data', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::sequence()->push([
                'status' => ['code' => 200],
                'response' => [
                    'results' => [
                        [
                            'data' => [
                                'meta' => ['cntabsolute' => 1000],
                                'records' => [['id' => 1]],
                            ],
                        ],
                    ],
                ],
            ])->push([
                'status' => ['code' => 500, 'message' => 'Second page failed'],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        expect(fn () => $onOfficeService->requestAll(function () {
            return app(OnOfficeService::class)->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::Estate,
            );
        }))->toThrow(OnOfficeException::class, 'Second page failed');

        Http::assertSentCount(2);
    });

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
    it('throws the request error', function (int $statusCode) {
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
    })->with([300, 301, 400, 401, 500, 501])->throws(OnOfficeException::class);

    it('throws when a later page fails instead of stopping silently', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::sequence()->push([
                'status' => ['code' => 200],
                'response' => [
                    'results' => [
                        [
                            'data' => [
                                'meta' => ['cntabsolute' => 1000],
                                'records' => [['id' => 1]],
                            ],
                        ],
                    ],
                ],
            ])->push([
                'status' => ['code' => 500, 'message' => 'Second page failed'],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $pages = collect();

        expect(fn () => $onOfficeService->requestAllChunked(function () {
            return app(OnOfficeService::class)->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::Estate,
            );
        }, function (array $records) use ($pages) {
            $pages->push($records);
        }))->toThrow(OnOfficeException::class, 'Second page failed');

        expect($pages)->toHaveCount(1);
        Http::assertSentCount(2);
    });

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
