<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\FieldBuilder;

class FieldRepository extends BaseRepository
{
    protected function createBuilder(): FieldBuilder
    {
        return new FieldBuilder;
    }
}
