<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\Builder;
use Katalam\OnOfficeAdapter\Query\UploadBuilder;

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
