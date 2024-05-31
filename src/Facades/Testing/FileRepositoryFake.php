<?php

namespace Katalam\OnOfficeAdapter\Facades\Testing;

use Katalam\OnOfficeAdapter\Query\Testing\UploadBuilderFake;

class FileRepositoryFake
{
    use FakeResponses;

    /**
     * Returns a new fake upload builder instance.
     */
    public function upload(): UploadBuilderFake
    {
        return new UploadBuilderFake($this->fakeResponses);
    }
}
