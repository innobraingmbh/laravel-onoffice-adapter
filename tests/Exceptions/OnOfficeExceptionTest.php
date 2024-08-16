<?php

declare(strict_types=1);

use Katalam\OnOfficeAdapter\Enums\OnOfficeError;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;

describe('is response error', function () {
    it('can be created', function () {
        $exception = new OnOfficeException('Test message', 123, isResponseError: true);

        expect($exception->getMessage())->toBe('Test message')
            ->and($exception->getCode())->toBe(123)
            ->and($exception->isResponseError())->toBeTrue();
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
