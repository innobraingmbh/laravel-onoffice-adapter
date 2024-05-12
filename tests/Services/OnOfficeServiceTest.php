<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

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
});
