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

    public function equals(self $other): bool
    {
        return $this->token === $other->token
            && $this->secret === $other->secret
            && $this->apiClaim === $other->apiClaim;
    }
}
