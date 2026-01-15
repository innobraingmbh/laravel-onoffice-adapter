# File Repository

Handle file uploads. The resource type is `uploadfile`.

## Upload & Link

```php
use Innobrain\OnOfficeAdapter\Facades\FileRepository;

// Upload in chunks, then link
$tmpId = FileRepository::upload()
    ->uploadInBlocks(20480)
    ->save(base64_encode($content));

FileRepository::upload()->link($tmpId, [
    'module' => 'estate',
    'relatedRecordId' => 409,
    'file' => 'document.pdf',
    'Art' => 'Dokument',
]);

// Or combined
FileRepository::upload()
    ->uploadInBlocks()
    ->saveAndLink(base64_encode($content), [
        'module' => 'estate',
        'relatedRecordId' => 409,
        'file' => 'photo.jpg',
        'Art' => 'Foto',
        'setDefaultPublicationRights' => true,
    ]);
```

## Modules

`estate`, `address`, `agentsLog`, `task`, `tmpUpload`

## Estate File Types

**Pictures**: `Titelbild`, `Foto`, `Foto_gross`, `Grundriss`, `Lageplan`, `Panorama`

**Documents**: `Expose`, `Dokument`, `Energieausweis`

**Links**: `Link`, `Ogulo-Link`, `Film-Link`, `Objekt-Link`

## Upload Links

```php
FileRepository::upload()->saveAndLink(null, [
    'module' => 'estate',
    'relatedRecordId' => 2651,
    'Art' => 'Ogulo-Link',
    'url' => 'https://example.com/tour',
]);
```

## Estate Options

| Option | Description |
|--------|-------------|
| `Art` | File type (required for estate) |
| `position` | Position in Files tab |
| `documentAttribute` | Special attribute (one per estate) |
| `applyWaterMark` | Add watermark |
