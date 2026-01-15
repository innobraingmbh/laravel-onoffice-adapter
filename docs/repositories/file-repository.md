# File Repository

Handle file uploads and linking in onOffice. The resource type is `uploadfile`. Files can be uploaded for estates, addresses, agents log, and tasks.

## Chunked Uploads

For large files, upload in chunks to avoid memory issues:

```php
use Innobrain\OnOfficeAdapter\Facades\FileRepository;

// Upload file content in chunks (default 20480 characters per chunk)
$tmpId = FileRepository::upload()
    ->uploadInBlocks(20480)
    ->save(base64_encode($fileContent));
```

## Linking Files

After uploading, link the file to a record:

```php
// Link to an estate
FileRepository::upload()
    ->link($tmpId, [
        'module' => 'estate',
        'relatedRecordId' => 409,
        'file' => 'document.pdf',
        'title' => 'Property Document',
        'Art' => 'Dokument', // Required for estate module
    ]);

// Link to an address
FileRepository::upload()
    ->link($tmpId, [
        'module' => 'address',
        'relatedRecordId' => 10505,
        'file' => 'contract.pdf',
    ]);

// Link to agents log
FileRepository::upload()
    ->link($tmpId, [
        'module' => 'agentsLog',
        'relatedRecordId' => 67075,
        'file' => 'email_attachment.pdf',
    ]);
```

## Combined Save & Link

Upload and link in a single operation:

```php
FileRepository::upload()
    ->uploadInBlocks(20480)
    ->saveAndLink(base64_encode($fileContent), [
        'module' => 'estate',
        'relatedRecordId' => 409,
        'file' => 'property_photo.jpg',
        'title' => 'Front View',
        'Art' => 'Foto',
        'setDefaultPublicationRights' => true,
    ]);
```

## File Types for Estates

When uploading to the `estate` module, the `Art` parameter is required:

### Picture Types

| Type | Description |
|------|-------------|
| `Titelbild` | Title image |
| `Foto` | Photo |
| `Foto_gross` | Large photo |
| `Grundriss` | Floor plan |
| `Lageplan` | Site plan |
| `Stadtplan` | City map |
| `Anzeigen` | Advertisements |
| `Epass_Skala` | Energy pass scale |
| `Finanzierungsbeispiel` | Financing example |
| `QR-Code` | QR code |
| `Logo` | Logo |
| `Banner` | Banner |
| `Panorama` | Panorama |

### Document Types

| Type | Category |
|------|----------|
| `Expose` | external |
| `Dokument` | internal |
| `Aushang` | external |
| `Mietaufstellung` | external |
| `Energieausweis` | external |

### Link Types

| Type | Description |
|------|-------------|
| `Link` | Standard link |
| `Ogulo-Link` | Ogulo link |
| `Film-Link` | Video link |
| `Objekt-Link` | Property link |

## Uploading Links

Upload a link instead of a file:

```php
FileRepository::upload()
    ->saveAndLink(null, [
        'module' => 'estate',
        'relatedRecordId' => 2651,
        'title' => 'Virtual Tour',
        'Art' => 'Ogulo-Link',
        'url' => 'https://example.com/virtual-tour',
    ]);
```

## Estate-Specific Options

```php
FileRepository::upload()
    ->uploadInBlocks()
    ->saveAndLink(base64_encode($fileContent), [
        'module' => 'estate',
        'relatedRecordId' => 409,
        'file' => 'photo.jpg',
        'Art' => 'Foto',
        'title' => 'Kitchen View',
        'freetext' => 'Additional notes about the image',
        'documentAttribute' => 'document_reservation_list', // One per estate
        'position' => 0, // Position in Files tab (0 = first)
        'setDefaultPublicationRights' => true, // Apply default publication settings
        'applyWaterMark' => true, // Add configured watermark
    ]);
```

::: warning
Each `documentAttribute` can only be assigned once per estate. Document attributes can be queried via the Field Repository with module `file`.
:::

## Temporary Upload (Cache)

Upload a file to temporary storage for later processing:

```php
$result = FileRepository::upload()
    ->uploadInBlocks()
    ->save(base64_encode($fileContent), [
        'module' => 'tmpUpload',
        'file' => 'temp_file.pdf',
        'title' => 'Temporary File',
    ]);

// Response contains cacheFileUuid for later use
$cacheFileUuid = $result['cacheFileUuid'];
```

## Return Values

| Method | Returns |
|--------|---------|
| `save()` | Temporary upload ID (tmpUploadId) |
| `link()` | File data array with fileId |
| `saveAndLink()` | File data array with fileId |

::: info
A temporary upload ID can only be used once. For very large uploads, consider adjusting chunk size carefully.
:::

## Supported Modules

| Module | Description |
|--------|-------------|
| `estate` | Property files and images |
| `address` | Address attachments |
| `agentsLog` | Activity attachments |
| `task` | Task attachments |
| `tmpUpload` | Temporary storage |
