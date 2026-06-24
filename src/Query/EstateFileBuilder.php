<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;

class EstateFileBuilder extends FileBuilder
{
    public function __construct(
        public int $estateId,
    ) {
        parent::__construct();
    }

    protected function resourceId(): OnOfficeResourceId
    {
        return OnOfficeResourceId::Estate;
    }

    protected function parentIdParameter(): string
    {
        return 'estateid';
    }

    protected function relationType(): string
    {
        return 'estate';
    }

    protected function parentId(): int
    {
        return $this->estateId;
    }
}
