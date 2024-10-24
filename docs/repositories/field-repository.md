# Field Repository

The field repository is useful to query all available fields of the client you are connecting to.
::: info
Depending on the client, fields can vary significantly. Clients often do not use the default fields, but inidvidually created fields.
:::

You can query one or more modules at once.
```php
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;

$fields = FieldRepository::query()
    ->withModules(['estate', 'address'])
    ->get();
    
$field = FieldRepository::query()
    ->withModules('estate')
    ->first();
```

```php
FieldRepository::query()
    ->withModules(['estate'])
    ->each(function (array $fields) {
        // First page
    });
```

You can also query fields with additional parameters.
```php
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;

FieldRepository::query()
    ->withModules(['estate'])
    ->parameters([
        'language' => 'ENG',
        'labels' => true,
    ]);
```
