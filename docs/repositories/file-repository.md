# File Repository

The File Repository provides functionality to upload and link files to records in the onOffice system. It supports chunked file uploads to handle large files efficiently.

## Upload Configuration

The upload process uses chunks based on UTF-8 characters. The default chunk size is 20480 characters.

::: info
Changing the chunk size can lead to OOM (Out Of Memory) or significant slowdown. We recommend sticking with the default value in most settings.
:::

## Operations

### Basic Upload and Link

```php
use Innobrain\OnOfficeAdapter\Facades\FileRepository;

// Upload file and get temporary ID
$tmpUploadId = FileRepository::query()
    ->uploadInBlocks(20480)
    ->save(base64_encode($fileContent));

// Link uploaded file to a record
$file = FileRepository::query()
    ->link($tmpUploadId, [
        'module' => 'estate',
        'relatedRecordId' => '12345',
    ]);
```

### Combined Upload and Link

```php
use Innobrain\OnOfficeAdapter\Facades\FileRepository;

// Upload and link in one operation
FileRepository::query()
    ->uploadInBlocks(20480)
    ->saveAndLink(base64_encode($fileContent), [
        'module' => 'estate',
        'relatedRecordId' => '12345',
        'file' => 'filename.pdf',
    ]);
```

## Available Methods

### Configuration Methods
- `uploadInBlocks(int $size)`: Set the chunk size for file uploads in characters

### Upload Methods
- `save(string $fileContent)`: Upload file content and return temporary upload ID
- `link(string $tmpUploadId, array $data)`: Link an uploaded file to a record
- `saveAndLink(string $fileContent, array $data)`: Combine upload and link operations

## Parameters

### Upload Parameters
- `fileContent`: Base64 encoded file content

### Link Parameters
Required fields:
- `module`: Target module (e.g., 'estate', 'address')
- `relatedRecordId`: ID of the record to link the file to
Optional fields:
- `file`: Original filename

## Returns

- `save()`: Returns temporary upload ID (string)
- `link()`: Returns linked file data (array)
- `saveAndLink()`: Returns linked file data (array)

## Exceptions

All methods may throw `OnOfficeException` for API-related errors
