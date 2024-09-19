# Field Repository

```php
use Katalam\OnOfficeAdapter\Facades\FieldRepository;

$fields = FieldRepository::query()
    ->withModules(['estate'])
    ->get();

$field = FieldRepository::query()
    ->withModules(['estate'])
    ->first();

FieldRepository::query()
    ->withModules(['estate'])
    ->each(function (array $fields) {
        // First page
    });
```

