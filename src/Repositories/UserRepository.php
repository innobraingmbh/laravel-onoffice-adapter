<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\UserBuilder;
use Innobrain\OnOfficeAdapter\Query\UserListBuilder;

class UserRepository extends BaseRepository
{
    protected function createBuilder(): UserBuilder
    {
        return new UserBuilder;
    }

    /**
     * Returns a new user list builder instance.
     */
    public function list(): UserListBuilder
    {
        /** @var UserListBuilder */
        return $this->createBuilderFromClass(UserListBuilder::class);
    }
}
