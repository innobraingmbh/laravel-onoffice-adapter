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
     */
    public const FIRST_RESULT = 'response.results.0';

    /**
     * All records of the first result block.
     */
    public const RECORDS = self::FIRST_RESULT.'.data.records';

    /**
     * The ids of every record in the first result block.
     */
    public const RECORD_IDS = self::RECORDS.'.*.id';

    /**
     * The first record of the first result block.
     */
    public const FIRST_RECORD = self::RECORDS.'.0';

    /**
     * The elements payload of the first record.
     */
    public const FIRST_RECORD_ELEMENTS = self::FIRST_RECORD.'.elements';

    /**
     * Whether the first record's action succeeded (write endpoints).
     */
    public const FIRST_RECORD_ELEMENTS_SUCCESS = self::FIRST_RECORD_ELEMENTS.'.success';

    /**
     * The temporary upload id returned when uploading a file.
     */
    public const FIRST_RECORD_ELEMENTS_TMP_UPLOAD_ID = self::FIRST_RECORD_ELEMENTS.'.tmpUploadId';

    /**
     * The resolved text returned when expanding a macro.
     */
    public const FIRST_RECORD_ELEMENTS_RESOLVED_TEXT = self::FIRST_RECORD_ELEMENTS.'.resolvedtext';

    /**
     * The absolute record count of the first result block (pagination).
     */
    public const META_COUNT_ABSOLUTE = self::FIRST_RESULT.'.data.meta.cntabsolute';

    /**
     * The error code of the first result block's status.
     */
    public const STATUS_ERROR_CODE = self::FIRST_RESULT.'.status.errorcode';

    /**
     * The message of the first result block's status.
     */
    public const STATUS_MESSAGE = self::FIRST_RESULT.'.status.message';
}
