<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\FieldBuilder;

class FieldRepository extends BaseRepository
{
    protected function createBuilder(): FieldBuilder
    {
        return new FieldBuilder;
    }
}
