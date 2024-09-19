<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories;

use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\Concerns\SuccessTrait;

class FileFactory extends BaseFactory
{
    use SuccessTrait;

    public string $type = 'file';
}
