<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\MacroBuilder;

class MacroRepository extends BaseRepository
{
    protected function createBuilder(): MacroBuilder
    {
        return new MacroBuilder;
    }
}
