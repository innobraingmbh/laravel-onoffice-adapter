<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query\Testing;

use Throwable;

class RelationBuilderFake extends BaseFake
{
    /**
     * @throws Throwable
     */
    public function create(): array
    {
        return $this->get()->first();
    }
}
