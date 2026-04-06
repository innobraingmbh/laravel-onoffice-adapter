<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\TaskBuilder;

class TaskRepository extends BaseRepository
{
    protected function createBuilder(): TaskBuilder
    {
        return new TaskBuilder;
    }
}
