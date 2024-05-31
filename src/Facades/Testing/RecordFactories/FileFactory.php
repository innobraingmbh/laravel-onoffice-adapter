<?php

namespace Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories;

use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\Concerns\SuccessTrait;

class FileFactory extends BaseFactory
{
    use SuccessTrait;

    public string $type = 'file';
}
