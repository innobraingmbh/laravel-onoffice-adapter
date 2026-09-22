<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Exceptions;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Throwable;

class ReadOnlyViolationException extends OnOfficeException
{
    public function __construct(
        public readonly OnOfficeRequest $request,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message === '' ? self::describe($request) : $message, $code, $previous);
    }

    private static function describe(OnOfficeRequest $request): string
    {
        $resourceType = $request->resourceType instanceof OnOfficeResourceType
            ? $request->resourceType->value
            : $request->resourceType;

        return sprintf(
            'Read-only mode: refused %s on %s.',
            $request->actionId->name,
            $resourceType === '' ? 'an unnamed resource' : $resourceType,
        );
    }
}
