<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\LastSeenBuilder;

class LastSeenRepository extends BaseRepository
{
    protected function createBuilder(): LastSeenBuilder
    {
        return new LastSeenBuilder;
    }
}
