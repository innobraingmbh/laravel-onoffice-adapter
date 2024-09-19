<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\RelationBuilder;

class RelationRepository extends BaseRepository
{
    protected function createBuilder(): RelationBuilder
    {
        return new RelationBuilder;
    }
}
