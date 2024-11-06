<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Dtos;

readonly class OnOfficeApiCredentials
{
    public function __construct(
        public string $token,
        public string $secret,
        public string $apiClaim = ''
    ) {}
}
