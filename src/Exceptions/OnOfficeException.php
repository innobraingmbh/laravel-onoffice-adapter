<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeError;
use Throwable;

class OnOfficeException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        private readonly bool $isResponseError = false,
        private readonly ?Response $originalResponse = null
    ) {
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

    public function getOriginalResponse(): ?Response
    {
        return $this->originalResponse;
    }
}
