<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeError;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;

describe('is response error', function () {
    it('can be created', function () {
        $guzzleResponse = new Response(500);
        $exception = new OnOfficeException('Test message', 123, isResponseError: true, originalResponse: new Illuminate\Http\Client\Response($guzzleResponse));

        expect($exception->getMessage())->toBe('Test message')
            ->and($exception->getCode())->toBe(123)
            ->and($exception->isResponseError())->toBeTrue()
            ->and($exception->getOriginalResponse()->status())->toBe(500);
    });
});

describe('get error', function () {
    it('can parse an error', function (int $errorCode) {
        $exception = new OnOfficeException('Test message', $errorCode, isResponseError: true);

        expect($exception->getError())->toBe(OnOfficeError::tryFrom($errorCode));
    })->with(OnOfficeError::values());

    it('will return unknown if response error is not known', function () {
        $exception = new OnOfficeException('Test message', 987, isResponseError: true);

        expect($exception->getError())->toBe(OnOfficeError::UNKNOWN);
    });
});

describe('get original response', function () {
    it('can be created', function () {
        $guzzleResponse = new Response(500);
        $exception = new OnOfficeException('Test message', 123, isResponseError: true, originalResponse: new Illuminate\Http\Client\Response($guzzleResponse));

        expect($exception->getOriginalResponse()->status())->toBe(500);
    });
});
