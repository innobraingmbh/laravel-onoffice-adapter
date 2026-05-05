<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\ActionBuilder;

class ActionRepository extends BaseRepository
{
    protected function createBuilder(): ActionBuilder
    {
        return new ActionBuilder;
    }
}
