# File Repository

```php
use Innobrain\OnOfficeAdapter\Facades\FileRepository;

$tmpUploadId = FileRepository::query()
    ->uploadInBlocks(5120)
    ->save(base64_encode($fileContent));

$file = FileRepository::query()
    ->link($tmpUploadId, [
        'module' => 'estate',
        'relatedRecordId' => '12345',
    ]);

FileRepository::query()
    ->uploadInBlocks(5120)
    ->saveAndLink(base64_encode($fileContent), [
        'module' => 'estate',
        'relatedRecordId' => '12345',
    ]);
```

