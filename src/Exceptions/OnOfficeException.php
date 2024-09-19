<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Exceptions;

use Exception;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeError;
use Throwable;

class OnOfficeException extends Exception
{
    private bool $isResponseError;

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, bool $isResponseError = false)
    {
        parent::__construct($message, $code, $previous);

        $this->isResponseError = $isResponseError;
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
