<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\ActivityBuilder;

class ActivityRepository extends BaseRepository
{
    protected function createBuilder(): ActivityBuilder
    {
        return new ActivityBuilder;
    }
}
