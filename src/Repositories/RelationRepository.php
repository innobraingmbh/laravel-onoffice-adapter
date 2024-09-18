<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\RelationBuilder;

class RelationRepository extends BaseRepository
{
    protected function createBuilder(): RelationBuilder
    {
        return new RelationBuilder;
    }
}
