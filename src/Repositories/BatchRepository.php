<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\BatchBuilder;

class BatchRepository extends BaseRepository
{
    protected function createBuilder(): BatchBuilder
    {
        return new BatchBuilder;
    }
}
