# Estate Repository

## Estates
```php
use Katalam\OnOfficeAdapter\Facades\EstateRepository;

$estates = EstateRepository::query()
    ->get();

$estate = EstateRepository::query()
    ->first();

$estate = EstateRepository::query()
    ->find(1);

EstateRepository::query()
    ->each(function (array $estates) {
        // First page
    });

EstateRepository::query()
    ->addModify('estate_id', 1)
    ->modify(1);

$estate = EstateRepository::query()
    ->create([
        'estate_id' => 1,
    ]);
```

## Estate Files
```php
use Katalam\OnOfficeAdapter\Facades\EstateRepository;

$files = EstateRepository::files(1)
    ->get();

$file = EstateRepository::files(1)
    ->first();

$file = EstateRepository::files(1)
    ->find(1);

EstateRepository::files(1)
    ->each(function (array $files) {
        // First page
    });

EstateRepository::files(1)
    ->addModify('file_id', 1)
    ->modify(1);

EstateRepository::files(1)
    ->delete(1);
```
