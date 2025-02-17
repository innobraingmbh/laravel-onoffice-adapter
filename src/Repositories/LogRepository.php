<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\LogBuilder;

class LogRepository extends BaseRepository
{
    protected function createBuilder(): LogBuilder
    {
        return new LogBuilder();
    }
}
