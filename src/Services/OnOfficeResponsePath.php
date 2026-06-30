<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Services;

/**
 * Dot-notation paths into the onOffice API JSON response envelope.
 *
 * onOffice wraps every payload in a fixed `response.results.0` structure.
 * These constants name the spots inside that envelope we read from, so the
 * shape lives in one place instead of being copy-pasted as a magic string
 * into every builder. Use with Laravel's `Response::json($path)` accessor.
 */
final class OnOfficeResponsePath
{
    /**
     * The single result block onOffice returns for a request.
     *
     * Resolves to `response.results.0`.
     */
    public const FIRST_RESULT = 'response.results.0';

    /**
     * All records of the first result block.
     *
     * Resolves to `response.results.0.data.records`.
     */
    public const RECORDS = self::FIRST_RESULT.'.data.records';

    /**
     * The ids of every record in the first result block.
     *
     * Resolves to `response.results.0.data.records.*.id`.
     */
    public const RECORD_IDS = self::RECORDS.'.*.id';

    /**
     * The first record of the first result block.
     *
     * Resolves to `response.results.0.data.records.0`.
     */
    public const FIRST_RECORD = self::RECORDS.'.0';

    /**
     * The elements payload of the first record.
     *
     * Resolves to `response.results.0.data.records.0.elements`.
     */
    public const FIRST_RECORD_ELEMENTS = self::FIRST_RECORD.'.elements';

    /**
     * Whether the first record's action succeeded (write endpoints).
     *
     * Resolves to `response.results.0.data.records.0.elements.success`.
     */
    public const FIRST_RECORD_ELEMENTS_SUCCESS = self::FIRST_RECORD_ELEMENTS.'.success';

    /**
     * The temporary upload id returned when uploading a file.
     *
     * Resolves to `response.results.0.data.records.0.elements.tmpUploadId`.
     */
    public const FIRST_RECORD_ELEMENTS_TMP_UPLOAD_ID = self::FIRST_RECORD_ELEMENTS.'.tmpUploadId';

    /**
     * The resolved text returned when expanding a macro.
     *
     * Resolves to `response.results.0.data.records.0.elements.resolvedtext`.
     */
    public const FIRST_RECORD_ELEMENTS_RESOLVED_TEXT = self::FIRST_RECORD_ELEMENTS.'.resolvedtext';

    /**
     * The absolute record count of the first result block (pagination).
     *
     * Resolves to `response.results.0.data.meta.cntabsolute`.
     */
    public const META_COUNT_ABSOLUTE = self::FIRST_RESULT.'.data.meta.cntabsolute';

    /**
     * The error code of the first result block's status.
     *
     * Resolves to `response.results.0.status.errorcode`.
     */
    public const STATUS_ERROR_CODE = self::FIRST_RESULT.'.status.errorcode';

    /**
     * The message of the first result block's status.
     *
     * Resolves to `response.results.0.status.message`.
     */
    public const STATUS_MESSAGE = self::FIRST_RESULT.'.status.message';
}
