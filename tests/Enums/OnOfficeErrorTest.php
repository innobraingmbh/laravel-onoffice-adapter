<?php

declare(strict_types=1);

use Katalam\OnOfficeAdapter\Enums\OnOfficeError;

describe('to string', function () {
    it('can stringify an error', function (OnOfficeError $error) {
        expect($error->toString())->toBeString()
            ->toBe(data_get(OnOfficeError::errorTexts(), $error->value, 'unknown error'));
    })->with(OnOfficeError::cases());
});
