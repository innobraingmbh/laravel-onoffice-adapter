<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use Innobrain\OnOfficeAdapter\Exceptions\IncompleteUploadException;
use Innobrain\OnOfficeAdapter\Facades\FileRepository;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

class ProbeUploadSizeCommand extends Command
{
    protected $signature = 'probe:upload-size
        {--bytes=50000 : Size of the random file in bytes.}
        {--block=20480 : Base64 characters per block.}';

    protected $description = 'Upload a random file in blocks to a temporary file, which is never linked to a record, and print the size onOffice reports after each block.';

    public function handle(): int
    {
        $bytes = max(1, (int) $this->option('bytes'));
        $content = base64_encode(random_bytes($bytes));

        FileRepository::record();

        try {
            $tmpUploadId = FileRepository::upload()
                ->uploadInBlocks((int) $this->option('block'))
                ->save($content);

            $this->components->info("save() accepted the upload as {$tmpUploadId}");
        } catch (IncompleteUploadException $exception) {
            $this->components->error($exception->getMessage());
        }

        $this->table(
            ['#', 'base64 chars sent', 'tmpUploadId', 'filesize reported'],
            FileRepository::recorded()->values()->map(fn (array $pair, int $index): array => [
                $index + 1,
                strlen((string) $pair[0]->parameters[OnOfficeService::DATA]),
                $pair[0]->parameters['tmpUploadId'] ?? '(new)',
                data_get($pair[1], OnOfficeResponsePath::FIRST_RECORD_ELEMENTS_FILESIZE),
            ])->all(),
        );

        $this->components->info("The file has {$bytes} bytes.");

        return self::SUCCESS;
    }
}
