<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\ActivityBuilder;

class ActivityRepository extends BaseRepository
{
    protected function createBuilder(): ActivityBuilder
    {
        return new ActivityBuilder;
    }
}
