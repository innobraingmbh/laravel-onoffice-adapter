<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Exceptions;

use Exception;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeError;
use Throwable;

class OnOfficeException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, private readonly bool $isResponseError = false)
    {
        parent::__construct($message, $code, $previous);
    }

    public function isResponseError(): bool
    {
        return $this->isResponseError;
    }

    public function getError(): OnOfficeError
    {
        return OnOfficeError::tryFrom($this->getCode()) ?? OnOfficeError::UNKNOWN;
    }
}
