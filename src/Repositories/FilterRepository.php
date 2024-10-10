<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\FilterBuilder;

class FilterRepository extends BaseRepository
{
    protected function createBuilder(): FilterBuilder
    {
        return new FilterBuilder;
    }
}
