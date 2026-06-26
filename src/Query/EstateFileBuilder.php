<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

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
        return OnOfficeService::ESTATEID;
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
