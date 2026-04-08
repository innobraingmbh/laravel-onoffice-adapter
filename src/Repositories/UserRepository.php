<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\UserBuilder;

class UserRepository extends BaseRepository
{
    protected function createBuilder(): UserBuilder
    {
        return new UserBuilder;
    }
}
