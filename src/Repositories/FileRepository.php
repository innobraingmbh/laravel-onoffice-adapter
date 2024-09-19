<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\Builder;
use Innobrain\OnOfficeAdapter\Query\UploadBuilder;

class FileRepository extends BaseRepository
{
    public function upload(): Builder
    {
        return $this->query();
    }

    protected function createBuilder(): UploadBuilder
    {
        return new UploadBuilder;
    }
}
