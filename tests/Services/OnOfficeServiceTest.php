<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeError;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Innobrain\OnOfficeAdapter\Tests\Stubs\InvalidHmacResponse;

describe('credentials', function () {
    it('can use the config once', function () {
        $token = Str::random(32);
        $secret = Str::random(64);
        $apiClaim = Str::random();

        Config::set([
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
        $token = Str::random(32);
        $secret = Str::random(64);
        $apiClaim = Str::random();

        Config::set([
            'onoffice.token' => 'old-token',
            'onoffice.secret' => 'old-secret',
            'onoffice.api_claim' => 'old-claim',
        ]);

        $onOfficeService = app(OnOfficeService::class);

        Config::set([
            'onoffice.token' => $token,
            'onoffice.secret' => $secret,
            'onoffice.api_claim' => $apiClaim,
        ]);

        expect($onOfficeService->getToken())->toBe($token)
            ->and($onOfficeService->getSecret())->toBe($secret)
            ->and($onOfficeService->getApiClaim())->toBe($apiClaim);
    });
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

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $onOfficeService->requestApi($request);
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

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        expect(fn () => $onOfficeService->requestApi($request))
            ->toThrow(OnOfficeException::class, 'Customer unknown!');
    });

    it('throws an exception on failed request inside response', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => InvalidHmacResponse::make(),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        expect(/**
         * @throws OnOfficeException
         */ static fn () => $onOfficeService->requestApi($request)
        )->toThrow(OnOfficeException::class, 'The HMAC is invalid');
    });

    it('throws an exception on failed request inside response with validation error for token and secret', function (int $tokenLength, int $secretLength, string $message) {
        Http::preventStrayRequests();
        Http::fake([
            '*' => InvalidHmacResponse::make(),
        ]);

        Config::set([
            'onoffice.token' => Str::random($tokenLength),
            'onoffice.secret' => Str::random($secretLength),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        expect(/**
         * @throws OnOfficeException
         */ static fn () => $onOfficeService->requestApi($request)
        )->toThrow(OnOfficeException::class, $message);
    })->with([
        [32, 64, 'The HMAC is invalid'],
        [31, 64, 'The HMAC is invalid. The token must be 32 characters, the secret 64 characters long.'],
        [33, 64, 'The HMAC is invalid. The token must be 32 characters, the secret 64 characters long.'],
        [32, 63, 'The HMAC is invalid. The token must be 32 characters, the secret 64 characters long.'],
        [32, 65, 'The HMAC is invalid. The token must be 32 characters, the secret 64 characters long.'],
    ]);

    it('throws an exception on failed request inside response without validation error for token and secret when other error', function (int $tokenLength, int $secretLength) {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'status' => [
                    'code' => 200,
                    'errorcode' => 0,
                    'message' => 'OK',
                ],
                'response' => [
                    'results' => [
                        [
                            'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:get',
                            'resourceid' => '',
                            'resourcetype' => 'actionkindtypes',
                            'cacheable' => false,
                            'identifier' => '',
                            'data' => [],
                            'status' => [
                                'errorcode' => 9,
                                'message' => 'Empty request',
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        Config::set([
            'onoffice.token' => Str::random($tokenLength),
            'onoffice.secret' => Str::random($secretLength),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        expect(/**
         * @throws OnOfficeException
         */ static fn () => $onOfficeService->requestApi($request)
        )->toThrow(OnOfficeException::class, 'Empty request');
    })->with([
        [32, 64],
        [31, 64],
        [33, 64],
        [32, 63],
        [32, 65],
    ]);

    it('can return an error', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => InvalidHmacResponse::make(),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        try {
            $onOfficeService->requestApi($request);
        } catch (OnOfficeException $exception) {
            expect($exception->getError())->toBe(OnOfficeError::The_HMAC_Is_Invalid);
        }
    });
});

describe('requestAll', function () {
    it('logs the request error', function (int $statusCode) {
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

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $onOfficeService->requestAll(function () use ($request) {
            app(OnOfficeService::class)->requestApi($request);
        });
    })->with([300, 301, 400, 401, 500, 501])->throws(OnOfficeException::class);

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

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $response = $onOfficeService->requestAll(fn () => app(OnOfficeService::class)->requestApi($request));

        expect($response)->toBeInstanceOf(Collection::class)
            ->toBeEmpty();
    });

    it('will stop with take amount', function () {
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
                                    'cntabsolute' => 50,
                                ],
                                'records' => [
                                    [
                                        'id' => 1,
                                    ],
                                    [
                                        'id' => 2,
                                    ],
                                    [
                                        'id' => 3,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $response = $onOfficeService->requestAll(fn () => app(OnOfficeService::class)->requestApi($request), limit: 1);

        expect($response)->toBeInstanceOf(Collection::class)
            ->toHaveCount(1);
    });

    it('can handle string resource type', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'status' => [
                    'code' => 200,
                ],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            'estate',
        );

        $onOfficeService->requestAll(fn () => app(OnOfficeService::class)->requestApi($request), limit: 1);

        Http::assertSentCount(1);
    });
});

describe('requestAllChunked', function () {
    it('logs the request error', function (int $statusCode) {
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

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $onOfficeService->requestAllChunked(function () use ($request) {
            app(OnOfficeService::class)->requestApi($request);
        }, function () {});
    })->with([300, 301, 400, 401, 500, 501])->throws(OnOfficeException::class);

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

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $onOfficeService->requestAllChunked(fn () => app(OnOfficeService::class)->requestApi($request), function () use ($callback) {
            $callback->call();
        });
    });

    it('will stop with take amount', function () {
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
                                    'cntabsolute' => 50,
                                ],
                                'records' => [
                                    [
                                        'id' => 1,
                                    ],
                                    [
                                        'id' => 2,
                                    ],
                                    [
                                        'id' => 3,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $count = 0;
        $onOfficeService->requestAllChunked(fn () => app(OnOfficeService::class)->requestApi($request), function ($elements) use (&$count) {
            $count += count($elements);
        }, limit: 1);

        expect($count)->toBe(1);
    });

    it('will stop with take amount on different pages', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::sequence([
                Http::response([
                    'status' => [
                        'code' => 200,
                    ],
                    'response' => [
                        'results' => [
                            [
                                'data' => [
                                    'meta' => [
                                        'cntabsolute' => 4,
                                    ],
                                    'records' => [
                                        [
                                            'id' => 1,
                                        ],
                                        [
                                            'id' => 2,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
                Http::response([
                    'status' => [
                        'code' => 200,
                    ],
                    'response' => [
                        'results' => [
                            [
                                'data' => [
                                    'meta' => [
                                        'cntabsolute' => 4,
                                    ],
                                    'records' => [
                                        [
                                            'id' => 3,
                                        ],
                                        [
                                            'id' => 4,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $count = 0;
        $onOfficeService->requestAllChunked(fn () => app(OnOfficeService::class)->requestApi($request), function ($elements) use (&$count) {
            $count += count($elements);
        }, pageSize: 2, limit: 3);

        expect($count)->toBe(3);
    });
});

describe('requestConcurrently', function () {
    it('logs the request error', function (int $statusCode) {
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

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $onOfficeService->requestConcurrently(function () use ($request) {
            app(OnOfficeService::class)->requestApi($request);
        });
    })->with([300, 301, 400, 401, 500, 501])->throws(OnOfficeException::class);

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

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $response = $onOfficeService->requestConcurrently(fn () => app(OnOfficeService::class)->requestApi($request));

        expect($response)->toBeInstanceOf(Collection::class)
            ->toBeEmpty();
    });

    it('will stop with take amount', function () {
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
                                    'cntabsolute' => 50,
                                ],
                                'records' => [
                                    [
                                        'id' => 1,
                                    ],
                                    [
                                        'id' => 2,
                                    ],
                                    [
                                        'id' => 3,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $response = $onOfficeService->requestConcurrently(fn () => app(OnOfficeService::class)->requestApi($request), limit: 1);

        expect($response)->toBeInstanceOf(Collection::class)
            ->toHaveCount(1);
    });

    it('request all concurrently', function () {
        Config::set('concurrency.default', 'sync');

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
                                    'cntabsolute' => 501,
                                ],
                                'records' => [
                                    [
                                        'id' => 1,
                                    ],
                                    [
                                        'id' => 2,
                                    ],
                                    [
                                        'id' => 3,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $response = $onOfficeService->requestConcurrently(fn () => app(OnOfficeService::class)->requestApi($request));

        // 6 items expected, since we overwrite the count absolute with 501 items
        // 501 > 500 = 2 pages with 3 items each = 6 items
        // the response will be duplicated, since we fake the same response for each request
        expect($response)->toBeInstanceOf(Collection::class)
            ->toHaveCount(6);
    });

    it('can handle string resource type', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'status' => [
                    'code' => 200,
                ],
            ]),
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            'estate',
        );

        $onOfficeService->requestConcurrently(fn () => app(OnOfficeService::class)->requestApi($request), limit: 1);

        Http::assertSentCount(1);
    });
});

describe('retry', function () {
    it('can use the retry count', function (int $given, int $expected) {
        Config::set([
            'onoffice.retry.count' => $given,
        ]);

        $onOfficeService = app(OnOfficeService::class);

        expect($onOfficeService->getRetryCount())->toBe($expected);
    })->with([
        [3, 3],
        [-1, 1],
        [0, 1],
    ]);

    it('can use the retry delay', function (int $given, int $expected) {
        Config::set([
            'onoffice.retry.delay' => $given,
        ]);

        $onOfficeService = app(OnOfficeService::class);

        expect($onOfficeService->getRetryDelay())->toBe($expected);
    })->with([
        [100, 100],
        [-100, 1],
        [0, 1],
    ]);

    it('can use the retry only on connection error', function (bool $given) {
        Config::set([
            'onoffice.retry.only_on_connection_error' => $given,
        ]);

        $onOfficeService = app(OnOfficeService::class);

        expect($onOfficeService->retryOnlyOnConnectionError())->toBe($given);
    })->with([true, false]);

    it('will use the retry settings', function () {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::sequence([
                Http::response(status: 500),
                Http::response([
                    'status' => [
                        'code' => 200,
                    ],
                ]),
            ]),
        ]);

        Config::set([
            'onoffice.retry.count' => 2,
            'onoffice.retry.only_on_connection_error' => false,
        ]);

        $onOfficeService = app(OnOfficeService::class);

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Estate,
        );

        $onOfficeService->requestApi($request);

        Http::assertSentCount(2);
    });
});
