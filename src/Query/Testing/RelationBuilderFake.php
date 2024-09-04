<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query\Testing;

use Katalam\OnOfficeAdapter\Query\Concerns\RelationTypes;
use Throwable;

class RelationBuilderFake extends BaseFake
{
    use RelationTypes;

    /**
     * @throws Throwable
     */
    public function create(): array
    {
        return $this->get()->first();
    }
}
