<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Enums;

enum OnOfficeError: int
{
    case NOT_AUTHENTICATED = 22;
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
            self::EXTENDED_CLAIM_MISSING_EMPTY_OR_INVALID->value => 'parameter extendedclaim is required, but missing, empty or invalid',
            self::UNKNOWN->value => 'unknown error',
        ];
    }
}
