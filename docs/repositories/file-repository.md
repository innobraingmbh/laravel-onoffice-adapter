# File Repository

Easily handle file uploads and linking in onOffice. Large files can be uploaded in chunks to avoid memory issues.

## Chunked Uploads
```php
use Innobrain\OnOfficeAdapter\Facades\FileRepository;

// Break file content into chunks (default 20480 characters)
$tmpId = FileRepository::upload()
    ->uploadInBlocks(20480)
    ->save(base64_encode($fileContent));
```

## Linking Files
```php
FileRepository::upload()
    ->link($tmpId, [
        'module' => 'estate',
        'relatedRecordId' => '12345',
    ]);
```

## Combined Save & Link
```php
FileRepository::upload()
    ->uploadInBlocks(20480)
    ->saveAndLink(base64_encode($fileContent), [
        'module' => 'estate',
        'relatedRecordId' => '12345',
        'file' => 'filename.pdf',
    ]);
```

## Return Values
- **`save()`**: Temporary upload ID
- **`link()`**: File data array
- **`saveAndLink()`**: File data array

::: info
If an upload is very large, consider adjusting chunk size with caution.
:::