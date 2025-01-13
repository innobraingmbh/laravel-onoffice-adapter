# Field Repository

The Field Repository provides functionality to query and retrieve field definitions from the onOffice system. This is particularly useful for understanding the available fields for different modules in your client's setup.

::: info
Depending on the client, fields can vary significantly. Clients often do not use the default fields, but individually created fields. Always query the available fields to ensure compatibility with your client's setup.
:::

## Query Operations

### Basic Queries

You can query fields for one or more modules:

```php
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;

// Query multiple modules
$fields = FieldRepository::query()
    ->withModules(['estate', 'address'])
    ->get();
    
// Query single module and get first field
$field = FieldRepository::query()
    ->withModules('estate')
    ->first();

// Process fields in chunks
FieldRepository::query()
    ->withModules(['estate'])
    ->each(function (array $fields) {
        // Process each chunk of fields
    });
```

### Advanced Queries

You can enhance your queries with additional parameters for more specific results:

```php
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;

$fields = FieldRepository::query()
    ->withModules(['estate'])
    ->parameters([
        'language' => 'ENG',  // Specify language for field labels
        'labels' => true,     // Include field labels
    ])
    ->get();
```

## Available Methods

### Configuration Methods
- `withModules(array|string $modules)`: Specify which modules to query fields for
- `parameters(array $params)`: Set additional query parameters

### Query Methods
- `get()`: Returns a Collection of all matching fields
- `first()`: Returns the first matching field or null
- `each(callable $callback)`: Processes fields in chunks using the provided callback

## Parameters

### Module Types
Common module types include:
- `estate`: Real estate property fields
- `address`: Contact and address fields
- And other module types specific to your onOffice setup

### Additional Parameters
- `language`: Specify the language for field labels (e.g., 'ENG', 'DEU')
- `labels`: Include field labels when set to true
- Other parameters as supported by the onOffice API

## Returns

- Query operations return either a Collection of fields or a single field array
- Each field contains information about:
  - Field name
  - Field type
  - Module association
  - Labels (if requested)
  - Other metadata specific to the field

## Exceptions

All methods may throw `OnOfficeException` for API-related errors

## Example Response

```php
[
    'field_name' => [
        'type' => 'string',
        'module' => 'estate',
        'label' => [
            'ENG' => 'Field Label',
            'DEU' => 'Feldbezeichnung'
        ],
        // Additional field metadata
    ]
]
