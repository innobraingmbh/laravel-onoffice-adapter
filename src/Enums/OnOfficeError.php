<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Enums;

enum OnOfficeError: int
{
    // error codes
    case CUSTOMER_UNKNOWN = 41;

    // result error codes
    case NOT_AUTHENTICATED = 22;
    case HMAC_INVALID = 137;
    case EXTENDED_CLAIM_MISSING_EMPTY_OR_INVALID = 193;
    case UNKNOWN = 999;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function errorTexts(): array
    {
        return [
            self::NOT_AUTHENTICATED->value => 'not authenticated',
            self::CUSTOMER_UNKNOWN->value => 'Customer unknown!',
            self::HMAC_INVALID->value => 'The HMAC is invalid',
            self::EXTENDED_CLAIM_MISSING_EMPTY_OR_INVALID->value => 'parameter extendedclaim is required, but missing, empty or invalid',
            self::UNKNOWN->value => 'unknown error',
        ];
    }

    public function toString(): string
    {
        return data_get(self::errorTexts(), $this->value, 'unknown error');
    }
}
