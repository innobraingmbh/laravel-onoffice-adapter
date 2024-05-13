<?php

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\UserBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class UserRepository
{
    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {
    }

    /**
     * Returns a new user builder instance.
     */
    public function query(): UserBuilder
    {
        return new UserBuilder($this->onOfficeService);
    }
}
