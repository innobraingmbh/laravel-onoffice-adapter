# File Repository

You can use the file repository to upload and link files to records.
Chunking of the upload is calculated in UTF-8 characters. The default chunk size is 20480 characters.

::: info
Changing the chunk size can lead to OOM or significant slowdown. We recommend sticking with the default value in most settings.
:::

```php
use Innobrain\OnOfficeAdapter\Facades\FileRepository;

$tmpUploadId = FileRepository::query()
    ->uploadInBlocks(20480)
    ->save(base64_encode($fileContent));

$file = FileRepository::query()
    ->link($tmpUploadId, [
        'module' => 'estate',
        'relatedRecordId' => '12345',
    ]);

FileRepository::query()
    ->uploadInBlocks(20480)
    ->saveAndLink(base64_encode($fileContent), [
        'module' => 'estate',
        'relatedRecordId' => '12345',
        'file' => 'filename.pdf',
    ]);
```

