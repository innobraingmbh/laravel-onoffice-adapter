# Field Repository

List available fields for different modules. This helps you discover which fields exist in your onOffice client, as these can vary.

## Basic Usage
```php
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;

// Multiple modules
$fields = FieldRepository::query()
    ->withModules(['estate', 'address'])
    ->get();

// Single module
$estateFields = FieldRepository::query()
    ->withModules('estate')
    ->get();
```

## Including Labels / Language
```php
$fields = FieldRepository::query()
    ->withModules('estate')
    ->parameters([
        'language' => 'ENG',
        'labels'   => true,
    ])
    ->get();
```

::: tip
Fields often differ per onOffice client. Always query first to confirm which fields exist.
:::

## Chunks and Single Retrieval
```php
$field = FieldRepository::query()
    ->withModules('estate')
    ->first();

FieldRepository::query()
    ->withModules(['estate'])
    ->each(function (array $fields) {
        // handle chunk of fields
    });
```