<?php

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\UploadBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class FileRepository
{
    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {
    }

    /**
     * Returns a new upload builder instance.
     */
    public function upload(): UploadBuilder
    {
        return new UploadBuilder($this->onOfficeService);
    }
}
