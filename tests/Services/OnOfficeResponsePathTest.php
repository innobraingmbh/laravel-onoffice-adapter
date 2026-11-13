<?php

declare(strict_types=1);

use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;

describe('envelope paths', function () {
    it('names the results array', function () {
        expect(OnOfficeResponsePath::RESULTS)->toBe('response.results');
    });

    it('anchors the first result block on the results array', function () {
        expect(OnOfficeResponsePath::FIRST_RESULT)->toBe('response.results.0');
    });

    it('names the request-level status paths', function () {
        expect(OnOfficeResponsePath::STATUS_CODE)->toBe('status.code')
            ->and(OnOfficeResponsePath::STATUS_ERROR_CODE)->toBe('status.errorcode')
            ->and(OnOfficeResponsePath::STATUS_MESSAGE)->toBe('status.message');
    });
});

describe('record paths', function () {
    it('builds the record paths from the first result block', function () {
        expect(OnOfficeResponsePath::RECORDS)->toBe('response.results.0.data.records')
            ->and(OnOfficeResponsePath::RECORD_IDS)->toBe('response.results.0.data.records.*.id')
            ->and(OnOfficeResponsePath::FIRST_RECORD)->toBe('response.results.0.data.records.0')
            ->and(OnOfficeResponsePath::META_COUNT_ABSOLUTE)->toBe('response.results.0.data.meta.cntabsolute');
    });
});

describe('indexed result paths', function () {
    it('resolves the result block at a given index', function () {
        expect(OnOfficeResponsePath::result(0))->toBe('response.results.0')
            ->and(OnOfficeResponsePath::result(2))->toBe('response.results.2');
    });

    it('resolves per-result status paths at a given index', function () {
        expect(OnOfficeResponsePath::statusErrorCode(1))->toBe('response.results.1.status.errorcode')
            ->and(OnOfficeResponsePath::statusMessage(1))->toBe('response.results.1.status.message');
    });
});
