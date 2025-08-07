<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\LinkBuilder;

class LinkRepository extends BaseRepository
{
    protected function createBuilder(): LinkBuilder
    {
        return new LinkBuilder;
    }
}
