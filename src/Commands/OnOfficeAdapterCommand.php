<?php

namespace Katalam\OnOfficeAdapter\Commands;

use Illuminate\Console\Command;

class OnOfficeAdapterCommand extends Command
{
    public $signature = 'onoffice-adapter';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
